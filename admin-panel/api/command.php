<?php
// command.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$device_id = $_GET['device_id'] ?? null;

if (!$device_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing device_id']);
    exit;
}

// Fetch pending commands
$stmt = $pdo->prepare("SELECT id, command, payload FROM commands WHERE device_id = ? AND status = 'pending' ORDER BY created_at ASC");
$stmt->execute([$device_id]);
$commands = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($commands) {
    // Mark as executed immediately for polling efficiency (or wait for ack)
    $stmt = $pdo->prepare("UPDATE commands SET status = 'executed' WHERE device_id = ? AND status = 'pending'");
    $stmt->execute([$device_id]);

    echo json_encode(['status' => 'success', 'commands' => $commands]);
} else {
    echo json_encode(['status' => 'success', 'commands' => []]);
}
?>
