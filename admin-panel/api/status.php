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
    $battery_level = $_GET['battery_level'] ?? null;
    $is_charging = isset($_GET['is_charging']) ? (int)$_GET['is_charging'] : null;

    $update_fields = ["last_seen = NOW()", "ip_address = ?"];
    $params = [$ip_address];

    if ($battery_level !== null) {
        $update_fields[] = "battery_level = ?";
        $params[] = (int)$battery_level;
    }
    if ($is_charging !== null) {
        $update_fields[] = "is_charging = ?";
        $params[] = (int)$is_charging;
    }

    $params[] = $device_id;
    $sql = "UPDATE devices SET " . implode(", ", $update_fields) . " WHERE device_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

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
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['status' => 'error', 'message' => 'Device not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
