<?php
// EHMS — Standalone draft-admissions list.
//
// Self-contained: opens its own PDO connection (no Database class, no Composer)
// so it can be dropped anywhere and run on its own at
// http://localhost/hms/database/draft_admissions_list.php
//
// Corrected against database/schema.sql:
//   * database is hms_db, not hms_database
//   * the patient table is patient_registrations, not patients
//   * there is no `name` or `age` column: the name is assembled from
//     first/middle/last and age is derived from date_of_birth
//   * the "in progress" status is DRAFT; DRAFT_PENDING is not in the ENUM
//
// If you want this inside the SPA instead, use the existing endpoint
// backend/api/draft_admissions.php?status=DRAFT, already consumed by
// frontend/pages/admissions.php and frontend/pages/ipd-management.php.

session_start();

// Patient data must not be public. The login flow stores user_id in the session
// (see backend/includes/functions.php); a standalone script has to check it
// itself because it does not load that helper.
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Not authenticated. Please log in first.');
}

$host    = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hms_db';

$draft_admissions = [];
$error = null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // FETCH ONLY DRAFT / IN-PROGRESS ADMISSIONS
    $stmt = $pdo->prepare(
        "SELECT d.*,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                p.hospital_number,
                p.gender,
                TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age
           FROM draft_admissions d
           JOIN patient_registrations p ON p.id = d.patient_id
          WHERE d.status = 'DRAFT'
          ORDER BY d.created_at DESC, d.id DESC"
    );
    $stmt->execute();
    $draft_admissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $draft_admissions = [];
    $error = "Could not fetch draft admissions: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Draft Admissions</title>
<style>
    body { font-family: system-ui, sans-serif; margin: 2rem; color: #1f2937; }
    h1 { font-size: 1.25rem; }
    table { border-collapse: collapse; width: 100%; font-size: .875rem; }
    th, td { border: 1px solid #e5e7eb; padding: .5rem .6rem; text-align: left; }
    th { background: #f9fafb; }
    .error { color: #b91c1c; }
    .empty { color: #6b7280; }
</style>
</head>
<body>
<h1>Draft Admissions (<?= count($draft_admissions) ?>)</h1>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php elseif (!$draft_admissions): ?>
    <p class="empty">No draft admissions.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Draft #</th>
                <th>Patient</th>
                <th>Hospital #</th>
                <th>Gender</th>
                <th>Age</th>
                <th>Admission Date</th>
                <th>Type</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($draft_admissions as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['draft_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($d['patient_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($d['hospital_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($d['gender'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($d['age'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($d['admission_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($d['admission_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($d['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</body>
</html>
