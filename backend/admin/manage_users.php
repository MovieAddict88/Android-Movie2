<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$page_title = 'Manage Customers';
$current_page = 'users';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Customer Directory</h2>
        <p class="text-gray-500 text-sm">View and manage registered customers in the system.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Customer</th>
                    <th class="px-6 py-4 font-semibold">Contact Info</th>
                    <th class="px-6 py-4 font-semibold">Registration Date</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach($users as $user): ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=random&size=128" class="w-10 h-10 rounded-full mr-3 shadow-sm">
                            <div>
                                <div class="font-bold text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                                <div class="text-[10px] text-gray-400 font-mono">ID: #<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($user['email']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($user['phone'] ?: 'No phone provided') ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600">
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </div>
                        <div class="text-[10px] text-gray-400">
                            <?= date('h:i A', strtotime($user['created_at'])) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-slate-400 hover:text-blue-600 transition" title="View Profile">
                                <i class="fas fa-user-circle"></i>
                            </button>
                            <button class="p-2 text-slate-400 hover:text-red-600 transition" title="Disable Account">
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($users)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                        <i class="fas fa-users-slash fa-3x mb-3 opacity-20"></i>
                        <p>No customers registered yet.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
