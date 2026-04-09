<?php
// api/devices_list.php
header('Content-Type: application/json');
require_once '../includes/db.php';

// Security check
validate_api_key();

if (!isset($pdo)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    $devices = $pdo->query("SELECT id, device_id, model, owner_name, is_locked, last_seen FROM devices")->fetchAll();
    echo json_encode($devices);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
