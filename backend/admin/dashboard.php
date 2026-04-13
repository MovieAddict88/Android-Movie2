<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

// Stats
$total_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$recent_bookings = $pdo->query("SELECT b.*, u.name as user_name, c.brand, c.model FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="bg-blue-800 text-white w-64 min-h-screen p-4">
        <h2 class="text-2xl font-bold mb-8 text-center">Admin Panel</h2>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block py-2.5 px-4 rounded bg-blue-900 transition"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
            <a href="manage_cars.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-car mr-2"></i> Manage Cars</a>
            <a href="manage_bookings.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-calendar-check mr-2"></i> Bookings</a>
            <a href="manage_payments.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-money-bill-wave mr-2"></i> Payments</a>
            <a href="manage_users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-users mr-2"></i> Customers</a>
            <a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-red-600 transition mt-8"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <div class="text-gray-600">Welcome, <?= $_SESSION['name'] ?></div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-600">
                <div class="text-gray-500 text-sm uppercase font-bold">Total Cars</div>
                <div class="text-3xl font-bold"><?= $total_cars ?></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-600">
                <div class="text-gray-500 text-sm uppercase font-bold">Total Bookings</div>
                <div class="text-3xl font-bold"><?= $total_bookings ?></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-600">
                <div class="text-gray-500 text-sm uppercase font-bold">Customers</div>
                <div class="text-3xl font-bold"><?= $total_users ?></div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="font-bold">Recent Bookings</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">Customer</th>
                        <th class="p-3 border-b">Car</th>
                        <th class="p-3 border-b">Dates</th>
                        <th class="p-3 border-b">Total</th>
                        <th class="p-3 border-b">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_bookings as $booking): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border-b"><?= $booking['id'] ?></td>
                        <td class="p-3 border-b"><?= $booking['user_name'] ?></td>
                        <td class="p-3 border-b"><?= $booking['brand'] . ' ' . $booking['model'] ?></td>
                        <td class="p-3 border-b"><?= $booking['start_date'] ?> to <?= $booking['end_date'] ?></td>
                        <td class="p-3 border-b font-bold">$<?= $booking['total_price'] ?></td>
                        <td class="p-3 border-b">
                            <span class="px-2 py-1 rounded-full text-xs <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-700' : ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
