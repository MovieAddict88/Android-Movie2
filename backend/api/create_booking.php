<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data['user_id'] ?? 0;
    $car_id = $data['car_id'] ?? 0;
    $start_date = $data['start_date'] ?? '';
    $end_date = $data['end_date'] ?? '';
    $with_driver = isset($data['with_driver']) && $data['with_driver'] ? 1 : 0;
    $include_carwash = isset($data['include_carwash']) && $data['include_carwash'] ? 1 : 0;

    // Calculate total price
    $stmt = $pdo->prepare("SELECT daily_rate FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();

    if ($car) {
        $d1 = new DateTime($start_date);
        $d2 = new DateTime($end_date);
        $days = $d1->diff($d2)->days;
        if ($days <= 0) $days = 1;
        $base_price = $days * $car['daily_rate'];
        
        $carwash_amount = 0;
        if ($include_carwash) {
            $carwash_amount = (float)getSetting('carwash_amount', '0.00');
        }
        
        $total_price = $base_price + $carwash_amount;

        // Downpayment calculation
        $dp_type = getSetting('downpayment_type', 'percentage');
        $dp_val = (float)getSetting('downpayment_value', '0');
        if ($dp_type === 'percentage') {
            $downpayment_amount = ($total_price * $dp_val) / 100;
        } else {
            $downpayment_amount = $dp_val;
        }

        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, with_driver, carwash_amount, downpayment_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $car_id, $start_date, $end_date, $total_price, $with_driver, $carwash_amount, $downpayment_amount])) {
            echo json_encode(['status' => 'success', 'message' => 'Booking created', 'booking_id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create booking']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Car not found']);
    }
}
?>
