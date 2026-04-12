<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

$user_id = $_GET['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT b.*, c.brand, c.model FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

echo json_encode([
    'status' => 'success',
    'bookings' => $bookings
]);
?>
