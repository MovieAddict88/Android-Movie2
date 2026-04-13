<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $data['user_id'] ?? null;
    $car_id = $data['car_id'] ?? null;
    $lat = $data['lat'] ?? null;
    $lng = $data['lng'] ?? null;
    
    // In a real app, we would update a 'trips' table here
    // For now, we'll just return success to simulate the interaction
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Location updated',
        'received' => [
            'lat' => $lat,
            'lng' => $lng
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
