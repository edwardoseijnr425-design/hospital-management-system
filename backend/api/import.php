<?php
/**
 * Bulk data import.
 *
 *   GET  ?action=schema   -> the whitelisted datasets and their columns
 *   POST ?action=import   -> { dataset, mode: insert|upsert, rows: [{col: value}] }
 *
 * Every target table and column comes from backend/includes/import_datasets.php,
 * so a request can never name an arbitrary table or column. Rows are validated
 * and coerced per the column types before any write; valid rows are committed
 * together, invalid rows are reported by spreadsheet line number and skipped.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/import_datasets.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();

if ($method === 'GET' && $action === 'schema') {
    jsonResponse([
        'success' => true,
        'datasets' => hmsImportDatasetList(),
    ]);
}

if ($method === 'GET' && $action === 'export') {
    handleExport();
}

if ($method === 'POST' && $action === 'import') {
    handleImport();
}

jsonResponse(['error' => 'Invalid action'], 400);

/* ------------------------------------------------------------------------- */

function handleImport() {
    // Bulk import writes directly to registers and master data, so it stays
    // with the administrative roles that already own those modules.
    requireRole(['super_admin', 'admin', 'it']);

    $data = getPostData();
    $datasetKey = $data['dataset'] ?? '';
    $mode = ($data['mode'] ?? 'insert') === 'upsert' ? 'upsert' : 'insert';
    $rows = $data['rows'] ?? [];

    if (!is_array($rows) || !$rows) {
        jsonResponse(['error' => 'No rows supplied'], 400);
    }
    if (count($rows) > 5000) {
        jsonResponse(['error' => 'Too many rows in one request (maximum 5000). Split the file and try again.'], 400);
    }

    $catalog = hmsImportDatasetCatalog();
    if (!isset($catalog[$datasetKey])) {
        jsonResponse(['error' => 'Unknown dataset'], 400);
    }
    $ds = $catalog[$datasetKey];

    // Upsert against a dataset with no unique key degrades to insert.
    $keyField = in_array($mode, ['upsert'], true) ? $ds['key_field'] : null;

    $db = Database::getInstance();
    $result = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    $db->beginTransaction();
    try {
        foreach ($rows as $i => $raw) {
            // Spreadsheet line number for the operator: +1 header +1 base 1.
            $line = $i + 2;
            try {
                applyRow($db, $ds, $keyField, $raw, $result);
            } catch (Exception $e) {
                $result['errors'][] = ['row' => $line, 'message' => $e->getMessage()];
                $result['skipped']++;
            }
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        jsonResponse(['error' => 'Import failed and was rolled back: ' . $e->getMessage()], 500);
    }

    logAudit('IMPORT', $ds['table'], null, null, [
        'dataset' => $datasetKey,
        'mode' => $mode,
        'inserted' => $result['inserted'],
        'updated' => $result['updated'],
        'skipped' => $result['skipped'],
    ]);

    jsonResponse(['success' => true] + $result);
}

function handleExport() {
    // Exporting master data uses the same administrative guard as import; in
    // particular the Users dataset is exported WITHOUT credential material.
    requireRole(['super_admin', 'admin', 'it']);

    $datasetKey = $_GET['dataset'] ?? '';
    $catalog = hmsImportDatasetCatalog();
    if (!isset($catalog[$datasetKey])) {
        jsonResponse(['error' => 'Unknown dataset'], 400);
    }
    $ds = $catalog[$datasetKey];

    // Column names come from the whitelist, never the request. The declared
    // password field is dropped so password hashes can never be exported.
    $columns = array_keys($ds['columns']);
    if (!empty($ds['password_field'])) {
        $columns = array_values(array_diff($columns, [$ds['password_field']]));
    }

    $db = Database::getInstance();
    $quoted = implode(', ', array_map(function ($c) { return '`' . $c . '`'; }, $columns));
    $rows = $db->fetchAll("SELECT {$quoted} FROM `{$ds['table']}`");

    $filename = 'hms_' . preg_replace('/[^a-z0-9_]+/i', '_', $datasetKey) . '_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    fputcsv($out, $columns);
    foreach ($rows as $row) {
        $line = [];
        foreach ($columns as $col) {
            $line[] = $row[$col] ?? null;
        }
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}

function applyRow($db, array $ds, $keyField, $raw, array &$result) {
    if (!is_array($raw)) {
        throw new Exception('Row is not an object');
    }

    // 1. Coerce only whitelisted columns.
    $record = [];
    foreach ($ds['columns'] as $column => $type) {
        if (!array_key_exists($column, $raw)) {
            continue;
        }
        $value = hmsCoerceValue($type, $raw[$column]);
        if ($value !== null) {
            $record[$column] = $value;
        }
    }

    // 2. Decide insert vs update.
    $existingId = null;
    if ($keyField) {
        $keyValue = $record[$keyField] ?? null;
        if ($keyValue === null || $keyValue === '') {
            throw new Exception("Missing key column '$keyField'");
        }
        $existingId = $db->fetchOne(
            "SELECT id FROM {$ds['table']} WHERE `$keyField` = ? LIMIT 1",
            [$keyValue]
        );
        $existingId = $existingId ? (int)$existingId['id'] : null;
    }

    if ($existingId === null) {
        // ---- Insert ----
        applyDefaults($ds, $record);
        $missing = [];
        foreach ($ds['required'] as $req) {
            if (!isset($record[$req]) || $record[$req] === '') {
                $missing[] = $req;
            }
        }
        // A user record needs a password to be created.
        if (isset($ds['password_field']) && empty($record[$ds['password_field']])) {
            $missing[] = $ds['password_field'];
        }
        if ($missing) {
            throw new Exception('Missing required field(s): ' . implode(', ', $missing));
        }
        hashSecret($ds, $record);
        $db->insert($ds['table'], $record);
        $result['inserted']++;
        return;
    }

    // ---- Update ----
    unset($record['id']);
    if (isset($ds['password_field']) && isset($record[$ds['password_field']])) {
        hashSecret($ds, $record);
    }
    if (!$record) {
        $result['skipped']++;
        return;
    }
    $db->update($ds['table'], $record, 'id = ?', [$existingId]);
    $result['updated']++;
}

// @user tokens (registered_by/created_by) resolve to the signed-in account.
function applyDefaults(array $ds, array &$record) {
    if (empty($ds['defaults'])) {
        return;
    }
    foreach ($ds['defaults'] as $column => $value) {
        if (array_key_exists($column, $record)) {
            continue;
        }
        if ($value === '@user') {
            $record[$column] = (int)getCurrentUserId();
        } elseif ($value === '@today') {
            $record[$column] = date('Y-m-d');
        } else {
            $record[$column] = $value;
        }
    }
}

function hashSecret(array $ds, array &$record) {
    $field = $ds['password_field'] ?? null;
    if ($field && !empty($record[$field])) {
        $record[$field] = hashPassword((string)$record[$field]);
    }
}

function hmsCoerceValue($type, $value) {
    if ($value === null) {
        return null;
    }
    if (is_string($value)) {
        // Spreadsheet cells often carry a non-breaking space.
        $value = trim(str_replace("\xC2\xA0", ' ', $value));
    }
    if ($value === '') {
        return null;
    }

    if (strpos($type, 'enum:') === 0) {
        $allowed = explode(',', substr($type, 5));
        foreach ($allowed as $option) {
            if (strcasecmp($option, (string)$value) === 0) {
                return $option;
            }
        }
        throw new Exception("'$value' is not one of: " . implode(', ', $allowed));
    }

    switch ($type) {
        case 'int':
            if (!is_numeric($value)) {
                throw new Exception("'$value' is not a whole number");
            }
            return (int)$value;

        case 'decimal':
            if (!is_numeric($value)) {
                throw new Exception("'$value' is not a number");
            }
            return (float)$value;

        case 'bool':
            return in_array(strtolower((string)$value), ['1', 'true', 'yes', 'y', 'active', 'on'], true) ? 1 : 0;

        case 'date':
            return hmsNormalizeDate($value);

        case 'datetime':
            return hmsNormalizeDateTime($value);

        default:
            return (string)$value;
    }
}

function hmsNormalizeDate($value) {
    if ($value instanceof DateTime) {
        return $value->format('Y-m-d');
    }
    $value = trim((string)$value);
    foreach (['Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
        $d = DateTime::createFromFormat('!' . $format, $value);
        if ($d && $d->format($format) === $value) {
            return $d->format('Y-m-d');
        }
    }
    $ts = strtotime($value);
    if ($ts === false) {
        throw new Exception("Invalid date '$value'");
    }
    return date('Y-m-d', $ts);
}

function hmsNormalizeDateTime($value) {
    if ($value instanceof DateTime) {
        return $value->format('Y-m-d H:i:s');
    }
    $value = trim((string)$value);
    foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'd/m/Y H:i', 'd/m/Y H:i:s'] as $format) {
        $d = DateTime::createFromFormat($format, $value);
        if ($d && $d->format($format) === $value) {
            return $d->format('Y-m-d H:i:s');
        }
    }
    $ts = strtotime($value);
    if ($ts === false) {
        throw new Exception("Invalid date/time '$value'");
    }
    return date('Y-m-d H:i:s', $ts);
}
