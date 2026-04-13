<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'app_name' => $_POST['app_name'],
        'carwash_amount' => $_POST['carwash_amount'],
        'downpayment_type' => $_POST['downpayment_type'],
        'downpayment_value' => $_POST['downpayment_value'],
    ];

    if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['app_logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = 'logo_' . time() . '.' . $ext;
            $target = '../assets/img/' . $new_name;
            if (!is_dir('../assets/img')) {
                mkdir('../assets/img', 0777, true);
            }
            if (move_uploaded_file($_FILES['app_logo']['tmp_name'], $target)) {
                $settings['app_logo'] = 'assets/img/' . $new_name;
            }
        }
    }

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$key, $value, $value]);
    }
    $message = 'Settings updated successfully!';
}

$app_name = getSetting('app_name', 'Car Rental');
$app_logo = getSetting('app_logo', 'assets/img/logo.png');
$carwash_amount = getSetting('carwash_amount', '0.00');
$downpayment_type = getSetting('downpayment_type', 'percentage');
$downpayment_value = getSetting('downpayment_value', '20');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Settings - Admin Panel</title>
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
            <a href="manage_users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-users mr-2"></i> Customers</a>
            <a href="manage_settings.php" class="block py-2.5 px-4 rounded bg-blue-900 transition"><i class="fas fa-cog mr-2"></i> Settings</a>
            <a href="tracking.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-map-marker-alt mr-2"></i> Live Tracking</a>
            <a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-red-600 transition mt-8"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">General Settings</h1>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">App Name</label>
                        <input type="text" name="app_name" value="<?= htmlspecialchars($app_name) ?>" class="w-full px-3 py-2 border rounded" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">App Logo</label>
                        <input type="file" name="app_logo" class="w-full px-3 py-2 border rounded">
                        <img src="../<?= $app_logo ?>" alt="Current Logo" class="mt-2 h-16">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Carwash Amount</label>
                        <input type="number" step="0.01" name="carwash_amount" value="<?= htmlspecialchars($carwash_amount) ?>" class="w-full px-3 py-2 border rounded" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Downpayment Type</label>
                        <select name="downpayment_type" class="w-full px-3 py-2 border rounded">
                            <option value="percentage" <?= $downpayment_type === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                            <option value="amount" <?= $downpayment_type === 'amount' ? 'selected' : '' ?>>Fixed Amount</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Downpayment Value</label>
                        <input type="number" step="0.01" name="downpayment_value" value="<?= htmlspecialchars($downpayment_value) ?>" class="w-full px-3 py-2 border rounded" required>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
