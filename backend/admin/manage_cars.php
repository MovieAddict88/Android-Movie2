<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Car deleted successfully!";
    }
}

// Handle Add/Edit (Simplified for now)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
    $brand = sanitize($_POST['brand']);
    $model = sanitize($_POST['model']);
    $type = sanitize($_POST['type']);
    $fuel = sanitize($_POST['fuel']);
    $transmission = sanitize($_POST['transmission']);
    $rate = (float)$_POST['rate'];
    $seats = (int)$_POST['seats'];
    $image = sanitize($_POST['image']);
    $has_dash_cam = isset($_POST['has_dash_cam']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO cars (brand, model, type, fuel_type, transmission, daily_rate, seating_capacity, image, has_dash_cam) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$brand, $model, $type, $fuel, $transmission, $rate, $seats, $image, $has_dash_cam])) {
        $success = "Car added successfully!";
    }
}

$cars = $pdo->query("SELECT * FROM cars ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Cars - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex">
    <!-- Sidebar (same as dashboard) -->
    <div class="bg-blue-800 text-white w-64 min-h-screen p-4">
        <h2 class="text-2xl font-bold mb-8 text-center">Admin Panel</h2>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
            <a href="manage_cars.php" class="block py-2.5 px-4 rounded bg-blue-900 transition"><i class="fas fa-car mr-2"></i> Manage Cars</a>
            <a href="manage_bookings.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-calendar-check mr-2"></i> Bookings</a>
            <a href="manage_payments.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-money-bill-wave mr-2"></i> Payments</a>
            <a href="manage_users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-users mr-2"></i> Customers</a>
            <a href="manage_settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-cog mr-2"></i> Settings</a>
            <a href="tracking.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-map-marker-alt mr-2"></i> Live Tracking</a>
            <a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-red-600 transition mt-8"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Manage Cars</h1>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"><i class="fas fa-plus mr-2"></i> Add New Car</button>
        </div>

        <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border-b">Image</th>
                        <th class="p-3 border-b">Brand/Model</th>
                        <th class="p-3 border-b">Type</th>
                        <th class="p-3 border-b">Dash Cam</th>
                        <th class="p-3 border-b">Daily Rate</th>
                        <th class="p-3 border-b">Status</th>
                        <th class="p-3 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cars as $car): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border-b">
                            <img src="<?= $car['image'] ?: 'https://via.placeholder.com/100x60' ?>" class="w-16 h-10 object-cover rounded">
                        </td>
                        <td class="p-3 border-b"><?= $car['brand'] . ' ' . $car['model'] ?></td>
                        <td class="p-3 border-b"><?= $car['type'] ?></td>
                        <td class="p-3 border-b"><?= $car['has_dash_cam'] ? 'Yes' : 'No' ?></td>
                        <td class="p-3 border-b font-bold">$<?= $car['daily_rate'] ?></td>
                        <td class="p-3 border-b">
                            <span class="<?= $car['availability_status'] ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $car['availability_status'] ? 'Available' : 'Unavailable' ?>
                            </span>
                        </td>
                        <td class="p-3 border-b">
                            <a href="?delete=<?= $car['id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Car Modal (Simplified) -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4">Add New Car</h2>
            <form action="" method="POST">
                <input type="hidden" name="add_car" value="1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold">Brand</label>
                        <input type="text" name="brand" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold">Model</label>
                        <input type="text" name="model" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold">Type</label>
                        <select name="type" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Luxury">Luxury</option>
                            <option value="Hatchback">Hatchback</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold">Seats</label>
                        <input type="number" name="seats" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" value="5" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold">Fuel</label>
                        <select name="fuel" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold">Transmission</label>
                        <select name="transmission" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold">Daily Rate ($)</label>
                    <input type="number" step="0.01" name="rate" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold">Image URL</label>
                    <input type="text" name="image" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="https://...">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="has_dash_cam" value="1" class="mr-2">
                        <span class="text-sm font-semibold">Has Dash Cam</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Car</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
