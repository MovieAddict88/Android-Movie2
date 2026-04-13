<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';

try {
    $stmt = $pdo->query("SELECT p.*, b.total_price, u.name as user_name FROM payments p JOIN bookings b ON p.booking_id = b.id JOIN users u ON b.user_id = u.id ORDER BY p.created_at DESC");
    $payments = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'payments' => $payments]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
