<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$where = "WHERE availability_status = 1";
$params = [];

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where .= " AND brand LIKE ?";
    $params[] = "%" . $_GET['brand'] . "%";
}

$stmt = $pdo->prepare("SELECT * FROM cars $where ORDER BY created_at DESC");
$stmt->execute($params);
$cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Cars - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Our Fleet</h1>

        <!-- Search Bar -->
        <form action="" method="GET" class="mb-12 max-w-md mx-auto flex gap-2">
            <input type="text" name="brand" placeholder="Search by brand..." class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?= isset($_GET['brand']) ? sanitize($_GET['brand']) : '' ?>">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Search</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if(empty($cars)): ?>
                <p class="col-span-full text-center text-gray-500">No cars found matching your criteria.</p>
            <?php else: ?>
                <?php foreach($cars as $car): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="<?= $car['image'] ?: 'https://via.placeholder.com/400x250?text=Car+Image' ?>" alt="<?= $car['brand'] ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-xl font-bold"><?= $car['brand'] . ' ' . $car['model'] ?></h3>
                            <span class="text-blue-600 font-bold">$<?= $car['daily_rate'] ?>/day</span>
                        </div>
                        <p class="text-gray-600 mb-2"><?= $car['type'] ?> | <?= $car['transmission'] ?> | <?= $car['fuel_type'] ?></p>
                        <?php if ($car['has_dash_cam']): ?>
                            <p class="text-xs text-green-600 mb-4"><i class="fas fa-video mr-1"></i> Dash Cam Included</p>
                        <?php else: ?>
                            <p class="mb-4"></p>
                        <?php endif; ?>
                        <a href="booking.php?id=<?= $car['id'] ?>" class="block text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Book Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
