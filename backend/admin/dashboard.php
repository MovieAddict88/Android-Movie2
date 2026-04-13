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
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'approved'")->fetchColumn() ?: 0;

$recent_bookings = $pdo->query("SELECT b.*, u.name as user_name, c.brand, c.model FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();

// Data for charts
$monthly_bookings_data = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%b') as month_name, 
        COUNT(*) as count 
    FROM bookings 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY MONTH(created_at)
    ORDER BY created_at ASC
")->fetchAll(PDO::FETCH_ASSOC);

$months = array_column($monthly_bookings_data, 'month_name');
$booking_counts = array_column($monthly_bookings_data, 'count');

$status_data = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM bookings 
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

$status_labels = array_column($status_data, 'status');
$status_counts = array_column($status_data, 'count');

$page_title = 'Dashboard Overview';
$current_page = 'dashboard';
$load_charts = true;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-car fa-4x text-blue-600"></i>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Total Cars</p>
        <h3 class="text-3xl font-bold text-gray-900"><?= $total_cars ?></h3>
        <div class="mt-4 flex items-center text-xs text-blue-600 font-semibold">
            <span class="bg-blue-50 px-2 py-1 rounded">Fleet size</span>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-calendar-check fa-4x text-green-600"></i>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Total Bookings</p>
        <h3 class="text-3xl font-bold text-gray-900"><?= $total_bookings ?></h3>
        <div class="mt-4 flex items-center text-xs text-green-600 font-semibold">
            <span class="bg-green-50 px-2 py-1 rounded">All time bookings</span>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-users fa-4x text-amber-600"></i>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Total Customers</p>
        <h3 class="text-3xl font-bold text-gray-900"><?= $total_users ?></h3>
        <div class="mt-4 flex items-center text-xs text-amber-600 font-semibold">
            <span class="bg-amber-50 px-2 py-1 rounded">Registered users</span>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-wallet fa-4x text-indigo-600"></i>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Total Revenue</p>
        <h3 class="text-3xl font-bold text-gray-900">$<?= number_format($total_revenue, 2) ?></h3>
        <div class="mt-4 flex items-center text-xs text-indigo-600 font-semibold">
            <span class="bg-indigo-50 px-2 py-1 rounded">Approved payments</span>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">Booking Activity</h3>
            <span class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Last 6 Months</span>
        </div>
        <div class="h-80">
            <canvas id="bookingChart"></canvas>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">Booking Status</h3>
        </div>
        <div class="h-64 flex items-center justify-center">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-2">
            <?php foreach($status_data as $index => $status): ?>
            <div class="flex items-center text-xs text-gray-500">
                <span class="w-3 h-3 rounded-full mr-2" style="background-color: <?= ['#fbbf24', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6'][$index] ?>;"></span>
                <?= ucfirst($status['status']) ?>: <?= $status['count'] ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Recent Bookings</h3>
        <a href="manage_bookings.php" class="inline-flex items-center px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg transition">
            View All Bookings <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Booking ID</th>
                    <th class="px-6 py-4 font-semibold">Customer</th>
                    <th class="px-6 py-4 font-semibold">Vehicle</th>
                    <th class="px-6 py-4 font-semibold">Duration</th>
                    <th class="px-6 py-4 font-semibold">Total</th>
                    <th class="px-6 py-4 font-semibold text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach($recent_bookings as $booking): ?>
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="px-6 py-4">
                        <span class="font-mono font-bold text-blue-600">#<?= $booking['id'] ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($booking['user_name']) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-700"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= date('M d', strtotime($booking['start_date'])) ?> - <?= date('M d, Y', strtotime($booking['end_date'])) ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">$<?= number_format($booking['total_price'], 2) ?></div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php
                        $status_classes = [
                            'confirmed' => 'bg-emerald-100 text-emerald-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'cancelled' => 'bg-rose-100 text-rose-700',
                            'completed' => 'bg-blue-100 text-blue-700'
                        ];
                        $class = $status_classes[$booking['status']] ?? 'bg-slate-100 text-slate-700';
                        ?>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $class ?>">
                            <?= $booking['status'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Common Chart Options
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // Booking Trends Chart
    const ctxBooking = document.getElementById('bookingChart').getContext('2d');
    const bookingGradient = ctxBooking.createLinearGradient(0, 0, 0, 400);
    bookingGradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
    bookingGradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

    new Chart(ctxBooking, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'Bookings',
                data: <?= json_encode($booking_counts) ?>,
                borderColor: '#2563eb',
                backgroundColor: bookingGradient,
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: '#f1f5f9' },
                    ticks: { stepSize: 1, color: '#64748b' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b' }
                }
            }
        }
    });

    // Status Distribution Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($status_labels) ?>,
            datasets: [{
                data: <?= json_encode($status_counts) ?>,
                backgroundColor: ['#fbbf24', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
