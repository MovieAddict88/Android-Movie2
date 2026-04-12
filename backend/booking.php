<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND availability_status = 1");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    redirect('cars.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $d1 = new DateTime($start_date);
    $d2 = new DateTime($end_date);
    $interval = $d1->diff($d2);
    $days = $interval->days;

    if ($days <= 0) {
        $error = "End date must be after start date.";
    } else {
        $total_price = $days * $car['daily_rate'];
        $user_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $car_id, $start_date, $end_date, $total_price])) {
            $success = "Booking requested successfully! Check your profile for status.";
        } else {
            $error = "Failed to create booking.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book <?= $car['brand'] ?> - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col md:flex-row">
            <div class="md:w-1/2">
                <img src="<?= $car['image'] ?: 'https://via.placeholder.com/600x400' ?>" class="w-full h-full object-cover">
            </div>
            <div class="md:w-1/2 p-8">
                <h1 class="text-3xl font-bold mb-2"><?= $car['brand'] . ' ' . $car['model'] ?></h1>
                <p class="text-blue-600 text-2xl font-bold mb-6">$<?= $car['daily_rate'] ?> / day</p>
                
                <?php if($error): ?>
                    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-1">Pickup Date</label>
                        <input type="date" name="start_date" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-600" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Return Date</label>
                        <input type="date" name="end_date" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-600" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
