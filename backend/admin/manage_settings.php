<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'app_name' => $_POST['app_name'],
        'carwash_amount' => $_POST['carwash_amount'],
        'downpayment_type' => $_POST['downpayment_type'],
        'downpayment_value' => $_POST['downpayment_value'],
    ];

    if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['app_logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = 'logo_' . time() . '.' . $ext;
            $target = '../assets/img/' . $new_name;
            if (!is_dir('../assets/img')) {
                mkdir('../assets/img', 0777, true);
            }
            if (move_uploaded_file($_FILES['app_logo']['tmp_name'], $target)) {
                $settings['app_logo'] = 'assets/img/' . $new_name;
            }
        }
    }

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$key, $value, $value]);
    }
    $message = 'Settings updated successfully!';
}

$app_name = getSetting('app_name', 'Car Rental');
$app_logo = getSetting('app_logo', 'assets/img/logo.png');
$carwash_amount = getSetting('carwash_amount', '0.00');
$downpayment_type = getSetting('downpayment_type', 'percentage');
$downpayment_value = getSetting('downpayment_value', '20');

$page_title = 'System Settings';
$current_page = 'settings';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Configuration</h2>
        <p class="text-gray-500 text-sm">Adjust your application preferences and business rules.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> <?= $message ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <form action="" method="POST" enctype="multipart/form-data" class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="space-y-6">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-2">Basic Info</h3>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Application Name</label>
                    <input type="text" name="app_name" value="<?= htmlspecialchars($app_name) ?>" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Application Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-2xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50">
                            <img src="../<?= $app_logo ?>" alt="Current Logo" class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="flex-1">
                            <input type="file" name="app_logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                            <p class="text-xs text-gray-400 mt-2">Recommended: PNG or SVG with transparent background.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-2">Business Rules</h3>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Carwash Fee ($)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                        <input type="number" step="0.01" name="carwash_amount" value="<?= htmlspecialchars($carwash_amount) ?>" class="w-full bg-gray-50 border border-gray-200 p-3 pl-8 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Downpayment Type</label>
                        <select name="downpayment_type" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition">
                            <option value="percentage" <?= $downpayment_type === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                            <option value="amount" <?= $downpayment_type === 'amount' ? 'selected' : '' ?>>Fixed Amount ($)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Downpayment Value</label>
                        <input type="number" step="0.01" name="downpayment_value" value="<?= htmlspecialchars($downpayment_value) ?>" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-6 border-t border-gray-100">
            <button type="submit" class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-lg shadow-blue-600/20">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
