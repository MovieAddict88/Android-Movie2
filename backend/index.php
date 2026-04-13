<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Fetch some featured cars
$stmt = $pdo->query("SELECT * FROM cars WHERE availability_status = 1 LIMIT 6");
$cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Car Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-gradient { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hero.jpg') center/cover; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-gradient h-[600px] flex items-center justify-center text-center text-white">
        <div>
            <h1 class="text-5xl md:text-6xl font-extrabold mb-4">Drive Your Dream Car Today</h1>
            <p class="text-xl mb-8">Premium cars for your luxury travel experiences.</p>
            <a href="cars.php" class="bg-blue-600 text-white px-8 py-4 rounded-full text-lg font-bold hover:bg-blue-700 transition duration-300">View All Cars</a>
        </div>
    </section>

    <!-- Featured Cars -->
    <section class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-12">Our Featured Fleet</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($cars as $car): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                <img src="<?= $car['image'] ?: 'https://via.placeholder.com/400x250?text=Car+Image' ?>" alt="<?= $car['brand'] ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-xl font-bold"><?= $car['brand'] . ' ' . $car['model'] ?></h3>
                        <span class="text-blue-600 font-bold">$<?= $car['daily_rate'] ?>/day</span>
                    </div>
                    <div class="flex space-x-4 text-gray-500 text-sm mb-4">
                        <span><i class="fas fa-user mr-1"></i> <?= $car['seating_capacity'] ?></span>
                        <span><i class="fas fa-gas-pump mr-1"></i> <?= $car['fuel_type'] ?></span>
                        <span><i class="fas fa-cog mr-1"></i> <?= $car['transmission'] ?></span>
                    </div>
                    <a href="booking.php?id=<?= $car['id'] ?>" class="block text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Book Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h4 class="text-2xl font-bold mb-4">CarRental</h4>
                <p class="text-gray-400">Making your travel comfortable and luxury.</p>
            </div>
            <div>
                <h4 class="font-bold mb-4">Quick Links</h4>
                <ul class="text-gray-400 space-y-2">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="cars.php">Cars</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Contact Us</h4>
                <p class="text-gray-400">Email: info@carrental.com</p>
                <p class="text-gray-400">Phone: +1 234 567 890</p>
            </div>
            <div>
                <h4 class="font-bold mb-4">Newsletter</h4>
                <div class="flex">
                    <input type="email" placeholder="Email" class="bg-gray-800 text-white px-4 py-2 rounded-l-lg focus:outline-none w-full">
                    <button class="bg-blue-600 px-4 py-2 rounded-r-lg hover:bg-blue-700">Join</button>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
