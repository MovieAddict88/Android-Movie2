<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <a href="index.php" class="text-2xl font-bold text-blue-600">CarRental</a>
            <div class="hidden md:flex space-x-8">
                <a href="index.php" class="text-gray-700 hover:text-blue-600">Home</a>
                <a href="cars.php" class="text-gray-700 hover:text-blue-600">Cars</a>
                <a href="map.php" class="text-gray-700 hover:text-blue-600">Map</a>
                <a href="contact.php" class="text-gray-700 hover:text-blue-600">Contact</a>
                <?php if(isLoggedIn()): ?>
                    <a href="profile.php" class="text-gray-700 hover:text-blue-600">My Bookings</a>
                    <?php if(isAdmin()): ?>
                        <a href="admin/dashboard.php" class="text-blue-600 font-semibold">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-red-500">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-700 hover:text-blue-600">Login</a>
                    <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
