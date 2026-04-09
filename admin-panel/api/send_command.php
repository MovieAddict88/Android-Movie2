<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$device_id = $_POST['device_id'] ?? null;
$command = $_POST['command'] ?? null;

if (!$device_id || !$command) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO commands (device_id, command) VALUES (?, ?)");
if ($stmt->execute([$device_id, $command])) {
    $is_locked = ($command == 'lock') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE devices SET is_locked = ? WHERE device_id = ?");
    $stmt->execute([$is_locked, $device_id]);

    echo json_encode(['status' => 'success', 'message' => 'Command sent']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
