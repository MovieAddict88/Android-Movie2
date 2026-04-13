<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $reference_number = $_POST['reference_number'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    
    if (empty($booking_id) || empty($payment_method) || empty($reference_number)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    $proof_of_payment = "";
    if (isset($_FILES['proof_of_payment'])) {
        $target_dir = "../uploads/payments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["proof_of_payment"]["name"], PATHINFO_EXTENSION);
        $proof_of_payment = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $proof_of_payment;
        if (!move_uploaded_file($_FILES["proof_of_payment"]["tmp_name"], $target_file)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload proof of payment']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, payment_method, reference_number, amount, proof_of_payment) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$booking_id, $payment_method, $reference_number, $amount, $proof_of_payment])) {
            echo json_encode(['status' => 'success', 'message' => 'Payment submitted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit payment']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
