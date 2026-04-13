<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Car deleted successfully!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
    $brand = sanitize($_POST['brand']);
    $model = sanitize($_POST['model']);
    $type = sanitize($_POST['type']);
    $fuel = sanitize($_POST['fuel']);
    $transmission = sanitize($_POST['transmission']);
    $rate = (float)$_POST['rate'];
    $seats = (int)$_POST['seats'];
    $image = sanitize($_POST['image']);
    $has_dash_cam = isset($_POST['has_dash_cam']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO cars (brand, model, type, fuel_type, transmission, daily_rate, seating_capacity, image, has_dash_cam) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$brand, $model, $type, $fuel, $transmission, $rate, $seats, $image, $has_dash_cam])) {
        $success = "Car added successfully!";
    }
}

$cars = $pdo->query("SELECT * FROM cars ORDER BY created_at DESC")->fetchAll();

$page_title = 'Manage Cars';
$current_page = 'cars';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Vehicle Fleet</h2>
        <p class="text-gray-500 text-sm">Manage your rental cars and their availability.</p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-lg shadow-blue-600/20">
        <i class="fas fa-plus mr-2"></i> Add New Car
    </button>
</div>

<?php if($success): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center">
        <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Vehicle</th>
                    <th class="px-6 py-4 font-semibold">Details</th>
                    <th class="px-6 py-4 font-semibold">Dash Cam</th>
                    <th class="px-6 py-4 font-semibold">Daily Rate</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach($cars as $car): ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <img src="<?= $car['image'] ?: 'https://via.placeholder.com/100x60?text=No+Image' ?>" class="w-16 h-10 object-cover rounded-lg mr-4 shadow-sm">
                            <div>
                                <div class="font-bold text-gray-900"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></div>
                                <div class="text-xs text-gray-500"><?= $car['type'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-600">
                            <span class="inline-block mr-2"><i class="fas fa-gas-pump mr-1"></i> <?= $car['fuel_type'] ?></span>
                            <span class="inline-block"><i class="fas fa-cog mr-1"></i> <?= $car['transmission'] ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if($car['has_dash_cam']): ?>
                            <span class="text-blue-600 bg-blue-50 px-2 py-1 rounded text-[10px] font-bold uppercase"><i class="fas fa-video mr-1"></i> Equipped</span>
                        <?php else: ?>
                            <span class="text-gray-400 text-[10px] uppercase font-bold">None</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">$<?= number_format($car['daily_rate'], 2) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if($car['availability_status']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span> Available
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span> Unavailable
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-slate-400 hover:text-blue-600 transition" title="Edit Car">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $car['id'] ?>" onclick="return confirm('Are you sure you want to delete this car?')" class="p-2 text-slate-400 hover:text-red-600 transition" title="Delete Car">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Car Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('addModal').classList.add('hidden')"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl relative z-10 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-800">Add New Vehicle</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="" method="POST" class="p-6">
                <input type="hidden" name="add_car" value="1">
                <div class="grid grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" placeholder="e.g. Toyota" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Model</label>
                        <input type="text" name="model" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" placeholder="e.g. Camry" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition">
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Luxury">Luxury</option>
                            <option value="Hatchback">Hatchback</option>
                            <option value="Van">Van</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Seating Capacity</label>
                        <input type="number" name="seats" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" value="5" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Fuel Type</label>
                        <select name="fuel" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition">
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Transmission</label>
                        <select name="transmission" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition">
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Daily Rate ($)</label>
                    <input type="number" step="0.01" name="rate" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" placeholder="0.00" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Image URL</label>
                    <input type="text" name="image" class="w-full bg-gray-50 border border-gray-200 p-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition" placeholder="https://example.com/car.jpg">
                </div>
                <div class="mb-6">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="has_dash_cam" value="1" class="sr-only peer">
                            <div class="w-10 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                        <span class="ml-3 text-sm font-semibold text-gray-700 group-hover:text-blue-600 transition">Equipped with Dash Cam</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-6 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
