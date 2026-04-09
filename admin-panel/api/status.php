<?php
// status.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$device_id = $_GET['device_id'] ?? null;

if (!$device_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing device_id']);
    exit;
}

$stmt = $pdo->prepare("SELECT rental_end_time, is_locked FROM devices WHERE device_id = ?");
$stmt->execute([$device_id]);
$device = $stmt->fetch(PDO::FETCH_ASSOC);

if ($device) {
    echo json_encode([
        'status' => 'success',
        'rental_end' => $device['rental_end_time'],
        'is_locked' => (bool)$device['is_locked']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Device not found']);
}
?>
