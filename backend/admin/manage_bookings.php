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

if (isset($_POST['assign_driver'])) {
    $booking_id = (int)$_POST['booking_id'];
    $driver_id = (int)$_POST['driver_id'];
    $stmt = $pdo->prepare("UPDATE bookings SET driver_id = ? WHERE id = ?");
    $stmt->execute([$driver_id, $booking_id]);
}

$bookings = $pdo->query("SELECT b.*, u.name as user_name, u.email as user_email, c.brand, c.model, d.name as driver_name FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id LEFT JOIN users d ON b.driver_id = d.id ORDER BY b.created_at DESC")->fetchAll();
$drivers = $pdo->query("SELECT id, name FROM users WHERE role = 'driver'")->fetchAll();
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
            <a href="manage_payments.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-money-bill-wave mr-2"></i> Payments</a>
            <a href="manage_users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-users mr-2"></i> Customers</a>
            <a href="manage_settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-cog mr-2"></i> Settings</a>
            <a href="tracking.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-map-marker-alt mr-2"></i> Live Tracking</a>
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
                        <th class="p-3 border-b">Details</th>
                        <th class="p-3 border-b">Total/DP</th>
                        <th class="p-3 border-b">Driver</th>
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
                        <td class="p-3 border-b text-xs">
                            With Driver: <?= $booking['with_driver'] ? 'Yes' : 'No' ?><br>
                            Carwash: $<?= $booking['carwash_amount'] ?>
                        </td>
                        <td class="p-3 border-b font-bold text-sm">
                            Total: $<?= $booking['total_price'] ?><br>
                            DP: $<?= $booking['downpayment_amount'] ?>
                        </td>
                        <td class="p-3 border-b">
                            <?php if($booking['with_driver']): ?>
                                <?php if($booking['driver_name']): ?>
                                    <span class="text-green-600"><?= $booking['driver_name'] ?></span>
                                <?php else: ?>
                                    <form action="" method="POST" class="flex items-center">
                                        <input type="hidden" name="assign_driver" value="1">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <select name="driver_id" class="text-xs border rounded p-1" required onchange="this.form.submit()">
                                            <option value="">Assign...</option>
                                            <?php foreach($drivers as $driver): ?>
                                                <option value="<?= $driver['id'] ?>"><?= $driver['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">N/A</span>
                            <?php endif; ?>
                        </td>
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
