<?php
// api/update_apps.php
header('Content-Type: application/json');
require_once '../includes/db.php';

// Security check
validate_api_key();

if (!isset($pdo)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['device_id']) || !isset($input['apps'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$device_id = $input['device_id'];
$apps = $input['apps']; // Array of objects {package_name, app_name, is_system}

try {
    $pdo->beginTransaction();

    // Option 1: Clear and re-insert (simpler)
    $stmt = $pdo->prepare("DELETE FROM device_apps WHERE device_id = ?");
    $stmt->execute([$device_id]);

    $stmt = $pdo->prepare("INSERT INTO device_apps (device_id, package_name, app_name, is_system_app) VALUES (?, ?, ?, ?)");
    foreach ($apps as $app) {
        $stmt->execute([
            $device_id,
            $app['package_name'],
            $app['app_name'] ?? $app['package_name'],
            isset($app['is_system']) ? (int)$app['is_system'] : 0
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'App list updated']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
