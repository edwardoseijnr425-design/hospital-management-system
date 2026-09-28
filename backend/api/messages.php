<?php
// EHMS — Messages & Alerts API
//   GET  ?action=recipients&type=patient|staff  -> recipient lists
//   GET  ?action=list&limit=N                   -> message feed
//   GET  ?action=unread_count                   -> unread counter for nav badge
//   POST ?action=send                           -> send a message
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

requireLogin();

if ($method === 'GET') {
    switch ($action) {
        case 'recipients':
            listRecipients();
            break;
        case 'list':
            listMessages();
            break;
        case 'unread_count':
            unreadCount();
            break;
        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
}

if ($method === 'POST' && $action === 'send') {
    sendMessage();
}

if ($method === 'PUT' && $action === 'read') {
    markRead();
}

jsonResponse(['error' => 'Invalid request'], 400);

function listRecipients() {
    $type = $_GET['type'] ?? 'patient';
    $db   = Database::getInstance();
    $term = trim($_GET['q'] ?? '');

    if ($type === 'patient') {
        $sql = "SELECT id,
                       CONCAT(TRIM(CONCAT(first_name, ' ', IFNULL(middle_name, ''))), ' ', last_name) AS name,
                       hospital_number
                FROM patient_registrations
                WHERE is_active = 1";
        $params = [];
        if ($term !== '') {
            $sql .= " AND (patient_registrations.hospital_number LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $like = '%' . $term . '%';
            $params = [$like, $like, $like];
        }
        $sql .= " ORDER BY first_name, last_name LIMIT 500";
        $rows = $db->fetchAll($sql, $params);
        jsonResponse(['success' => true, 'recipients' => $rows]);
    }

    $sql = "SELECT u.id,
                   u.full_name AS name,
                   u.username,
                   u.role,
                   IFNULL(d.name, '') AS department
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE u.is_active = 1";
    $params = [];
    if ($term !== '') {
        $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ?)";
        $like = '%' . $term . '%';
        $params = [$like, $like];
    }
    $sql .= " ORDER BY u.full_name LIMIT 500";
    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'recipients' => $rows]);
}

function listMessages() {
    $limit = min(max((int)($_GET['limit'] ?? 100), 1), 500);
    $db    = Database::getInstance();

    $sql = "SELECT m.id, m.sender_id, m.recipient_type, m.recipient_id, m.subject, m.message,
                   m.channel, m.patient_context_id, m.is_read, m.sent_at,
                   CASE
                     WHEN m.recipient_type = 'patient' THEN CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name)
                     ELSE IFNULL(u.full_name, CONCAT('User #', m.recipient_id))
                   END AS recipient_name,
                   s.full_name AS sender_name
            FROM messages m
            LEFT JOIN patient_registrations p ON m.recipient_type = 'patient' AND p.id = m.recipient_id
            LEFT JOIN users u ON m.recipient_type = 'staff' AND u.id = m.recipient_id
            LEFT JOIN users s ON s.id = m.sender_id
            ORDER BY m.sent_at DESC
            LIMIT {$limit}";

    $messages = $db->fetchAll($sql);
    jsonResponse(['success' => true, 'messages' => $messages]);
}

function unreadCount() {
    $db = Database::getInstance();
    $userId = getCurrentUserId();

    $count = $db->fetchOne(
        "SELECT COUNT(*) AS c FROM messages
         WHERE recipient_type = 'staff' AND recipient_id = ? AND is_read = 0",
        [$userId]
    )['c'] ?? 0;

    jsonResponse(['success' => true, 'unread' => (int)$count]);
}

function sendMessage() {
    $data = getPostData();

    $required = ['recipient_type', 'recipient_id', 'subject', 'message'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $recipientType = ($data['recipient_type'] === 'patient') ? 'patient' : 'staff';
    $recipientId   = (int)$data['recipient_id'];
    $channel       = in_array($data['channel'] ?? '', ['internal', 'sms', 'email'], true) ? $data['channel'] : 'internal';
    $contextId     = !empty($data['patient_context_id']) ? (int)$data['patient_context_id'] : null;

    $db = Database::getInstance();

    // Validate recipient exists
    if ($recipientType === 'patient') {
        $exists = $db->fetchOne("SELECT id FROM patient_registrations WHERE id = ? AND is_active = 1", [$recipientId]);
    } else {
        $exists = $db->fetchOne("SELECT id FROM users WHERE id = ? AND is_active = 1", [$recipientId]);
    }
    if (!$exists) {
        jsonResponse(['error' => 'Selected recipient no longer exists'], 400);
    }

    if ($contextId !== null) {
        $ctx = $db->fetchOne("SELECT id FROM patient_registrations WHERE id = ? AND is_active = 1", [$contextId]);
        if (!$ctx) $contextId = null;
    }

    try {
        $messageId = $db->insert('messages', [
            'sender_id'          => getCurrentUserId(),
            'recipient_type'     => $recipientType,
            'recipient_id'       => $recipientId,
            'subject'            => trim($data['subject']),
            'message'            => trim($data['message']),
            'channel'            => $channel,
            'patient_context_id' => $contextId,
        ]);
        logAudit('CREATE', 'messages', $messageId, null, ['subject' => trim($data['subject']), 'recipient_type' => $recipientType]);
        jsonResponse(['success' => true, 'message_id' => $messageId]);
    } catch (Exception $e) {
        error_log("Message send error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to send message'], 500);
    }
}

function markRead() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Message ID required'], 400);

    $db = Database::getInstance();
    try {
        $db->update('messages', ['is_read' => 1], 'id = ? AND recipient_id = ? AND recipient_type = ?', [$id, getCurrentUserId(), 'staff']);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to update message'], 500);
    }
}