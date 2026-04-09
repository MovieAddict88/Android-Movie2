<?php
// api/command.php
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

// Fetch pending commands
try {
    $stmt = $pdo->prepare("SELECT id, command, payload FROM commands WHERE device_id = ? AND status = 'pending' ORDER BY created_at ASC");
    $stmt->execute([$device_id]);
    $commands = $stmt->fetchAll();

    if ($commands) {
        // Mark as executed immediately for polling efficiency (or wait for ack)
        $stmt = $pdo->prepare("UPDATE commands SET status = 'executed' WHERE device_id = ? AND status = 'pending'");
        $stmt->execute([$device_id]);

        echo json_encode(['status' => 'success', 'commands' => $commands]);
    } else {
        echo json_encode(['status' => 'success', 'commands' => []]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
