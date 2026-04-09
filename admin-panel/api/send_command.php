<?php
// api/send_command.php
header('Content-Type: application/json');
require_once '../includes/db.php';

// Security check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allow either API Key (for Admin App) or Session (for Web Panel)
if (!isset($_SESSION['user_id'])) {
    validate_api_key();
}

if (!isset($pdo)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$device_id = $_POST['device_id'] ?? null;
$command = $_POST['command'] ?? null;
$payload = $_POST['payload'] ?? null;

if (!$device_id || !$command) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO commands (device_id, command, payload) VALUES (?, ?, ?)");
    if ($stmt->execute([$device_id, $command, $payload])) {
        // Update device status if lock/unlock
        if ($command == 'lock' || $command == 'unlock') {
            $is_locked = ($command == 'lock') ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE devices SET is_locked = ? WHERE device_id = ?");
            $stmt->execute([$is_locked, $device_id]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Command ' . htmlspecialchars($command) . ' sent to ' . htmlspecialchars($device_id)]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
