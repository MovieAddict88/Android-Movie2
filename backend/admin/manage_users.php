<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="bg-blue-800 text-white w-64 min-h-screen p-4">
        <h2 class="text-2xl font-bold mb-8 text-center">Admin Panel</h2>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
            <a href="manage_cars.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-car mr-2"></i> Manage Cars</a>
            <a href="manage_bookings.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-calendar-check mr-2"></i> Bookings</a>
            <a href="manage_payments.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-money-bill-wave mr-2"></i> Payments</a>
            <a href="manage_users.php" class="block py-2.5 px-4 rounded bg-blue-900 transition"><i class="fas fa-users mr-2"></i> Customers</a>
            <a href="manage_settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-cog mr-2"></i> Settings</a>
            <a href="tracking.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-map-marker-alt mr-2"></i> Live Tracking</a>
            <a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-red-600 transition mt-8"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Manage Customers</h1>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">Name</th>
                        <th class="p-3 border-b">Email</th>
                        <th class="p-3 border-b">Phone</th>
                        <th class="p-3 border-b">Joined At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border-b"><?= $user['id'] ?></td>
                        <td class="p-3 border-b"><?= $user['name'] ?></td>
                        <td class="p-3 border-b"><?= $user['email'] ?></td>
                        <td class="p-3 border-b"><?= $user['phone'] ?: 'N/A' ?></td>
                        <td class="p-3 border-b"><?= $user['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
