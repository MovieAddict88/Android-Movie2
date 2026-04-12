<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT b.*, c.brand, c.model, c.image FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8">My Bookings</h1>

        <?php if(empty($bookings)): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center">
                <p class="text-gray-500 mb-4">You haven't made any bookings yet.</p>
                <a href="cars.php" class="text-blue-600 font-bold">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach($bookings as $booking): ?>
                <div class="bg-white rounded-lg shadow-md p-6 flex flex-col md:flex-row items-center gap-6">
                    <img src="<?= $booking['image'] ?: 'https://via.placeholder.com/200x120' ?>" class="w-48 h-32 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold"><?= $booking['brand'] . ' ' . $booking['model'] ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?= $booking['start_date'] ?> to <?= $booking['end_date'] ?></p>
                        <p class="font-bold text-blue-600">Total: $<?= $booking['total_price'] ?></p>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-2 rounded-full text-sm font-bold <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-700' : ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
