<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

$settings = [
    'app_name' => getSetting('app_name', 'Car Rental'),
    'app_logo' => getSetting('app_logo', 'assets/img/logo.png'),
    'carwash_amount' => getSetting('carwash_amount', '0.00'),
    'downpayment_type' => getSetting('downpayment_type', 'percentage'),
    'downpayment_value' => getSetting('downpayment_value', '20'),
];

echo json_encode(['status' => 'success', 'settings' => $settings]);
?>
