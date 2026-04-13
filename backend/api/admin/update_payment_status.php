<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $payment_id = $data['payment_id'] ?? 0;
    $action = $data['action'] ?? ''; // 'approved' or 'rejected'
    $rejection_reason = $data['rejection_reason'] ?? '';

    if (empty($payment_id) || empty($action)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE payments SET status = ?, rejection_reason = ? WHERE id = ?");
        if ($stmt->execute([$action, $rejection_reason, $payment_id])) {
            if ($action === 'approved') {
                $stmt = $pdo->prepare("SELECT booking_id FROM payments WHERE id = ?");
                $stmt->execute([$payment_id]);
                $payment = $stmt->fetch();
                if ($payment) {
                    $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
                    $stmt->execute([$payment['booking_id']]);
                }
            }
            echo json_encode(['status' => 'success', 'message' => 'Payment status updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update payment status']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
