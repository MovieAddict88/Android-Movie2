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

$page_title = 'Manage Payments';
$current_page = 'payments';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Financial Transactions</h2>
        <p class="text-gray-500 text-sm">Review and verify customer payments and proofs.</p>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Transaction</th>
                    <th class="px-6 py-4 font-semibold">Customer</th>
                    <th class="px-6 py-4 font-semibold">Method</th>
                    <th class="px-6 py-4 font-semibold">Reference #</th>
                    <th class="px-6 py-4 font-semibold">Amount</th>
                    <th class="px-6 py-4 font-semibold">Proof</th>
                    <th class="px-6 py-4 font-semibold text-center">Status</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach($payments as $payment): ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <span class="font-mono font-bold text-slate-400 text-xs">TRX-<?= str_pad($payment['id'], 5, '0', STR_PAD_LEFT) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($payment['user_name']) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] font-bold uppercase">
                            <?= htmlspecialchars($payment['payment_method']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-mono text-gray-600"><?= htmlspecialchars($payment['reference_number']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">$<?= number_format($payment['amount'], 2) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($payment['proof_of_payment']): ?>
                            <a href="../uploads/payments/<?= $payment['proof_of_payment'] ?>" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-xs font-semibold underline decoration-blue-200 underline-offset-4">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> View Proof
                            </a>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs italic">No proof provided</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php
                        $status_classes = [
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'rejected' => 'bg-rose-100 text-rose-700'
                        ];
                        $class = $status_classes[$payment['status']] ?? 'bg-slate-100 text-slate-700';
                        ?>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $class ?>">
                            <?= $payment['status'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <?php if ($payment['status'] === 'pending'): ?>
                            <div class="flex justify-end gap-2">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                    <input type="hidden" name="action" value="approved">
                                    <button type="submit" class="bg-emerald-500 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition text-[10px] font-bold uppercase shadow-sm shadow-emerald-200">
                                        Approve
                                    </button>
                                </form>
                                <button onclick="showRejectModal(<?= $payment['id'] ?>)" class="bg-rose-500 text-white px-3 py-1.5 rounded-lg hover:bg-rose-600 transition text-[10px] font-bold uppercase shadow-sm shadow-rose-200">
                                    Reject
                                </button>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($payments)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                        <i class="fas fa-money-bill-wave fa-3x mb-3 opacity-20"></i>
                        <p>No payment transactions found.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="hideRejectModal()"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-800">Reject Payment</h3>
                <button onclick="hideRejectModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="p-6">
                <input type="hidden" name="payment_id" id="modal_payment_id">
                <input type="hidden" name="action" value="rejected">
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for rejection</label>
                    <textarea name="rejection_reason" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-rose-500 focus:bg-white outline-none transition min-h-[100px]" placeholder="Explain why this payment is being rejected..." required></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="hideRejectModal()" class="px-6 py-2 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-rose-500 text-white font-semibold hover:bg-rose-600 transition shadow-lg shadow-rose-500/20">Reject Payment</button>
                </div>
            </form>
        </div>
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

<?php include 'includes/footer.php'; ?>
