<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['action'] === 'confirm' ? 'confirmed' : ($_GET['action'] === 'cancel' ? 'cancelled' : '');
    if ($status) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
}

$bookings = $pdo->query("SELECT b.*, u.name as user_name, u.email as user_email, c.brand, c.model FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id ORDER BY b.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings - Admin</title>
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
            <a href="manage_bookings.php" class="block py-2.5 px-4 rounded bg-blue-900 transition"><i class="fas fa-calendar-check mr-2"></i> Bookings</a>
            <a href="manage_users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-users mr-2"></i> Customers</a>
            <a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-red-600 transition mt-8"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-8">
        <h1 class="text-3xl font-bold mb-8">All Bookings</h1>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">Customer</th>
                        <th class="p-3 border-b">Car</th>
                        <th class="p-3 border-b">Dates</th>
                        <th class="p-3 border-b">Total</th>
                        <th class="p-3 border-b">Status</th>
                        <th class="p-3 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border-b"><?= $booking['id'] ?></td>
                        <td class="p-3 border-b">
                            <?= $booking['user_name'] ?><br>
                            <span class="text-xs text-gray-500"><?= $booking['user_email'] ?></span>
                        </td>
                        <td class="p-3 border-b"><?= $booking['brand'] . ' ' . $booking['model'] ?></td>
                        <td class="p-3 border-b text-sm">
                            <?= $booking['start_date'] ?> to <?= $booking['end_date'] ?>
                        </td>
                        <td class="p-3 border-b font-bold">$<?= $booking['total_price'] ?></td>
                        <td class="p-3 border-b">
                            <span class="px-2 py-1 rounded-full text-xs <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-700' : ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </td>
                        <td class="p-3 border-b">
                            <?php if($booking['status'] === 'pending'): ?>
                                <a href="?action=confirm&id=<?= $booking['id'] ?>" class="text-green-600 hover:text-green-900 mr-2" title="Confirm"><i class="fas fa-check"></i></a>
                                <a href="?action=cancel&id=<?= $booking['id'] ?>" class="text-red-600 hover:text-red-900" title="Cancel"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
