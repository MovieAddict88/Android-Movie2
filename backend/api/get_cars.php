<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

$stmt = $pdo->query("SELECT * FROM cars WHERE availability_status = 1");
$cars = $stmt->fetchAll();

echo json_encode([
    'status' => 'success',
    'cars' => $cars
]);
?>
