<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$booking_id = $_GET['booking_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Check if booking belongs to user
$stmt = $pdo->prepare("SELECT b.*, c.brand, c.model FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    $reference_number = $_POST['reference_number'];
    $amount = $booking['total_price'];
    
    $proof_of_payment = "";
    if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] == 0) {
        $target_dir = "uploads/payments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["proof_of_payment"]["name"], PATHINFO_EXTENSION);
        $proof_of_payment = time() . '_' . uniqid() . '.' . $file_extension;
        move_uploaded_file($_FILES["proof_of_payment"]["tmp_name"], $target_dir . $proof_of_payment);
    }

    $stmt = $pdo->prepare("INSERT INTO payments (booking_id, payment_method, reference_number, amount, proof_of_payment) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$booking_id, $payment_method, $reference_number, $amount, $proof_of_payment])) {
        $_SESSION['success'] = "Payment submitted successfully! Waiting for admin approval.";
        redirect('profile.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Payment - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-2xl mx-auto px-4 py-12">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-2xl font-bold mb-6">Submit Payment</h1>
            <div class="mb-6 p-4 bg-blue-50 rounded">
                <p class="font-bold">Booking for: <?= $booking['brand'] . ' ' . $booking['model'] ?></p>
                <p class="text-xl text-blue-700">Amount to pay: $<?= $booking['total_price'] ?></p>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="w-full border rounded px-3 py-2" required onchange="updatePaymentInstructions()">
                        <option value="GCash">GCash</option>
                        <option value="PayMaya">PayMaya</option>
                        <option value="Coins.ph">Coins.ph</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>

                <div id="payment_instructions" class="mb-4 p-4 bg-gray-100 rounded text-sm italic">
                    <!-- Instructions will be updated by JS -->
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Reference Number</label>
                    <input type="text" name="reference_number" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Proof of Payment (Screenshot)</label>
                    <input type="file" name="proof_of_payment" class="w-full" accept="image/*" required>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition">Submit Payment</button>
            </form>
        </div>
    </div>

    <script>
        function updatePaymentInstructions() {
            const method = document.getElementById('payment_method').value;
            const instructions = document.getElementById('payment_instructions');
            
            if (method === 'Bank Transfer') {
                instructions.innerHTML = "Please transfer to:<br>Bank: BDO<br>Account Name: Car Rental Inc<br>Account Number: 00123456789";
            } else {
                instructions.innerHTML = "Please send your payment to our " + method + " account:<br>Name: Car Rental Inc<br>Number: 09123456789";
            }
        }
        updatePaymentInstructions();
    </script>
</body>
</html>
