<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$devices = $pdo->query("SELECT id, device_id, model, owner_name, is_locked FROM devices")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($devices);
?>
