<?php
// api/status.php
header('Content-Type: application/json');
require_once '../includes/db.php';

// Security check
validate_api_key();

if (!isset($pdo)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$device_id = $_GET['device_id'] ?? null;

if (!$device_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing device_id']);
    exit;
}

try {
    // Also update last_seen and ip_address when status is checked (like a heartbeat)
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("UPDATE devices SET last_seen = NOW(), ip_address = ? WHERE device_id = ?");
    $stmt->execute([$ip_address, $device_id]);

    $stmt = $pdo->prepare("SELECT rental_end_time, is_locked FROM devices WHERE device_id = ?");
    $stmt->execute([$device_id]);
    $device = $stmt->fetch();

    if ($device) {
        echo json_encode([
            'status' => 'success',
            'rental_end' => $device['rental_end_time'],
            'is_locked' => (bool)$device['is_locked']
        ]);
    } else {
        // If device is not in system, maybe auto-register with default values?
        // For now, return error
        echo json_encode(['status' => 'error', 'message' => 'Device not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
