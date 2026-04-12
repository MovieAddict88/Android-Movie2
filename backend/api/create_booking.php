<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data['user_id'] ?? 0;
    $car_id = $data['car_id'] ?? 0;
    $start_date = $data['start_date'] ?? '';
    $end_date = $data['end_date'] ?? '';

    // Calculate total price
    $stmt = $pdo->prepare("SELECT daily_rate FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();

    if ($car) {
        $d1 = new DateTime($start_date);
        $d2 = new DateTime($end_date);
        $days = $d1->diff($d2)->days;
        if ($days <= 0) $days = 1;
        $total_price = $days * $car['daily_rate'];

        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $car_id, $start_date, $end_date, $total_price])) {
            echo json_encode(['status' => 'success', 'message' => 'Booking created']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create booking']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Car not found']);
    }
}
?>
