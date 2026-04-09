<?php
// api/get_device_apps.php
header('Content-Type: application/json');
require_once '../includes/db.php';

// Security check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$device_id = $_GET['device_id'] ?? null;
if (!$device_id) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT package_name, app_name, is_system_app FROM device_apps WHERE device_id = ? ORDER BY app_name ASC");
    $stmt->execute([$device_id]);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
