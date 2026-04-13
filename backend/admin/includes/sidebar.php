<div class="bg-slate-900 text-white w-64 flex-shrink-0 flex flex-col">
    <div class="p-6">
        <h2 class="text-2xl font-bold tracking-wider text-blue-400">CAR<span class="text-white">RENTAL</span></h2>
        <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest">Administrator</p>
    </div>
    <nav class="flex-1 px-4 space-y-1">
        <a href="dashboard.php" class="flex items-center py-3 px-4 rounded-lg <?= ($current_page == 'dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> transition">
            <i class="fas fa-chart-line w-6"></i> Dashboard
        </a>
        <a href="manage_cars.php" class="flex items-center py-3 px-4 rounded-lg <?= ($current_page == 'cars') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> transition">
            <i class="fas fa-car w-6"></i> Manage Cars
        </a>
        <a href="manage_bookings.php" class="flex items-center py-3 px-4 rounded-lg <?= ($current_page == 'bookings') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> transition">
            <i class="fas fa-calendar-alt w-6"></i> Bookings
        </a>
        <a href="manage_payments.php" class="flex items-center py-3 px-4 rounded-lg <?= ($current_page == 'payments') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> transition">
            <i class="fas fa-credit-card w-6"></i> Payments
        </a>
        <a href="manage_users.php" class="flex items-center py-3 px-4 rounded-lg <?= ($current_page == 'users') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> transition">
            <i class="fas fa-users w-6"></i> Customers
        </a>
        <a href="manage_settings.php" class="flex items-center py-3 px-4 rounded-lg <?= ($current_page == 'settings') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> transition">
            <i class="fas fa-sliders-h w-6"></i> Settings
        </a>
        <a href="tracking.php" class="flex items-center py-3 px-4 rounded-lg <?= ($current_page == 'tracking') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> transition">
            <i class="fas fa-map-marked-alt w-6"></i> Live Tracking
        </a>
    </nav>
    <div class="p-4 border-t border-slate-800">
        <a href="../logout.php" class="flex items-center py-3 px-4 rounded-lg text-red-400 hover:bg-red-900/20 transition">
            <i class="fas fa-sign-out-alt w-6"></i> Logout
        </a>
    </div>
</div>

<div class="flex-1 flex flex-col">
    <!-- Topbar -->
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-8">
        <h1 class="text-xl font-semibold text-gray-800"><?= $page_title ?? 'Dashboard' ?></h1>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($_SESSION['name']) ?></p>
                <p class="text-xs text-gray-500">Super Admin</p>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=0D8ABC&color=fff" class="w-10 h-10 rounded-full">
        </div>
    </header>
    <main class="p-8">
