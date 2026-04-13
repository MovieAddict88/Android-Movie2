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

$page_title = 'Manage Bookings';
$current_page = 'bookings';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Booking Management</h2>
        <p class="text-gray-500 text-sm">Monitor and manage all customer vehicle reservations.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Booking ID</th>
                    <th class="px-6 py-4 font-semibold">Customer</th>
                    <th class="px-6 py-4 font-semibold">Vehicle</th>
                    <th class="px-6 py-4 font-semibold">Dates & Options</th>
                    <th class="px-6 py-4 font-semibold">Financials</th>
                    <th class="px-6 py-4 font-semibold text-center">Driver</th>
                    <th class="px-6 py-4 font-semibold text-center">Status</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach($bookings as $booking): ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <span class="font-mono font-bold text-blue-600">#<?= $booking['id'] ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($booking['user_name']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($booking['user_email']) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-800"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-600 space-y-1">
                            <div><i class="far fa-calendar-alt mr-1 text-blue-500"></i> <?= date('M d', strtotime($booking['start_date'])) ?> - <?= date('M d, Y', strtotime($booking['end_date'])) ?></div>
                            <div>
                                <i class="fas fa-user-tie mr-1 <?= $booking['with_driver'] ? 'text-green-500' : 'text-gray-300' ?>"></i> Driver: <?= $booking['with_driver'] ? 'Yes' : 'No' ?>
                                <i class="fas fa-broom ml-2 mr-1 <?= $booking['carwash_amount'] > 0 ? 'text-blue-500' : 'text-gray-300' ?>"></i> Wash: <?= $booking['carwash_amount'] > 0 ? 'Yes' : 'No' ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">
                            <div class="font-bold text-gray-900">$<?= number_format($booking['total_price'], 2) ?></div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-tight">DP: $<?= number_format($booking['downpayment_amount'], 2) ?></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if($booking['with_driver']): ?>
                            <?php if($booking['driver_name']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 text-xs font-semibold">
                                    <i class="fas fa-id-card mr-1.5"></i> <?= htmlspecialchars($booking['driver_name']) ?>
                                </span>
                            <?php else: ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="assign_driver" value="1">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <select name="driver_id" class="text-[10px] border border-blue-200 rounded-lg p-1 bg-blue-50 text-blue-700 focus:ring-2 focus:ring-blue-500 outline-none" required onchange="this.form.submit()">
                                        <option value="">Assign Driver...</option>
                                        <?php foreach($drivers as $driver): ?>
                                            <option value="<?= $driver['id'] ?>"><?= $driver['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-300 text-xs">-</span>
                        <?php endif; ?>
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
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-1">
                            <?php if($booking['status'] === 'pending'): ?>
                                <a href="?action=confirm&id=<?= $booking['id'] ?>" class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-lg transition" title="Confirm Booking">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                                <a href="?action=cancel&id=<?= $booking['id'] ?>" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Cancel Booking">
                                    <i class="fas fa-times-circle"></i>
                                </a>
                            <?php endif; ?>
                            <button class="p-2 text-slate-400 hover:bg-slate-50 rounded-lg transition" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($bookings)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-20"></i>
                        <p>No bookings found in the system.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
