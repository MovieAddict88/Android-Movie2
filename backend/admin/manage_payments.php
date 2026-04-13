<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

// Handle Approval/Rejection
if (isset($_POST['action']) && isset($_POST['payment_id'])) {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action']; // 'approved' or 'rejected'
    $rejection_reason = $_POST['rejection_reason'] ?? '';

    $stmt = $pdo->prepare("UPDATE payments SET status = ?, rejection_reason = ? WHERE id = ?");
    if ($stmt->execute([$action, $rejection_reason, $payment_id])) {
        // If approved, maybe update booking status too
        if ($action === 'approved') {
            $stmt = $pdo->prepare("SELECT booking_id FROM payments WHERE id = ?");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch();
            if ($payment) {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
                $stmt->execute([$payment['booking_id']]);
            }
        }
        $_SESSION['success'] = "Payment status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update payment status.";
    }
    redirect('manage_payments.php');
}

$payments = $pdo->query("SELECT p.*, b.total_price, u.name as user_name FROM payments p JOIN bookings b ON p.booking_id = b.id JOIN users u ON b.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Payments - Admin Panel</title>
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
            <a href="manage_payments.php" class="block py-2.5 px-4 rounded bg-blue-900 transition"><i class="fas fa-money-bill-wave mr-2"></i> Payments</a>
            <a href="manage_users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition"><i class="fas fa-users mr-2"></i> Customers</a>
            <a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-red-600 transition mt-8"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Manage Payments</h1>
            <div class="text-gray-600">Welcome, <?= $_SESSION['name'] ?></div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">User</th>
                        <th class="p-3 border-b">Method</th>
                        <th class="p-3 border-b">Ref #</th>
                        <th class="p-3 border-b">Amount</th>
                        <th class="p-3 border-b">Proof</th>
                        <th class="p-3 border-b">Status</th>
                        <th class="p-3 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $payment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border-b"><?= $payment['id'] ?></td>
                        <td class="p-3 border-b"><?= $payment['user_name'] ?></td>
                        <td class="p-3 border-b"><?= $payment['payment_method'] ?></td>
                        <td class="p-3 border-b"><?= $payment['reference_number'] ?></td>
                        <td class="p-3 border-b font-bold">$<?= $payment['amount'] ?></td>
                        <td class="p-3 border-b">
                            <?php if ($payment['proof_of_payment']): ?>
                                <a href="../uploads/payments/<?= $payment['proof_of_payment'] ?>" target="_blank" class="text-blue-600 hover:underline">View Proof</a>
                            <?php else: ?>
                                No proof
                            <?php endif; ?>
                        </td>
                        <td class="p-3 border-b">
                            <span class="px-2 py-1 rounded-full text-xs <?= $payment['status'] === 'approved' ? 'bg-green-100 text-green-700' : ($payment['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                                <?= ucfirst($payment['status']) ?>
                            </span>
                        </td>
                        <td class="p-3 border-b">
                            <?php if ($payment['status'] === 'pending'): ?>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                    <input type="hidden" name="action" value="approved">
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition text-xs">Approve</button>
                                </form>
                                <button onclick="showRejectModal(<?= $payment['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition text-xs">Reject</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-xl font-bold mb-4">Reject Payment</h3>
            <form method="POST">
                <input type="hidden" name="payment_id" id="modal_payment_id">
                <input type="hidden" name="action" value="rejected">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Reason for rejection</label>
                    <textarea name="rejection_reason" class="w-full border rounded p-2" required></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideRejectModal()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal(id) {
            document.getElementById('modal_payment_id').value = id;
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</body>
</html>
