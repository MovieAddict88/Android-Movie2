<?php
// register.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['device_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$device_id = $input['device_id'];
$model = $input['model'] ?? 'Unknown';

// Security check
validate_api_key();

// Validation
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $device_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Device ID format']);
    exit;
}

if (strlen($device_id) > 100 || strlen($model) > 100) {
    echo json_encode(['status' => 'error', 'message' => 'Input too long']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO devices (device_id, model) VALUES (?, ?) ON DUPLICATE KEY UPDATE model = ?");
if ($stmt->execute([$device_id, $model, $model])) {
    echo json_encode(['status' => 'success', 'message' => 'Device registered']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
