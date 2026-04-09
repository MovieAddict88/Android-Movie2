<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    if (isset($_POST['add_device'])) {
        $device_id = sanitize_input($_POST['device_id']);
        $model = sanitize_input($_POST['model']);
        $owner = sanitize_input($_POST['owner_name']);
        $rental_end = $_POST['rental_end'];

        // Validation
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $device_id)) {
            $error = "Invalid Device ID format";
        } elseif (strlen($device_id) > 100 || strlen($model) > 100 || strlen($owner) > 100) {
            $error = "Input too long";
        } else {
            $stmt = $pdo->prepare("INSERT INTO devices (device_id, model, owner_name, rental_end_time) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE model=?, owner_name=?, rental_end_time=?");
            $stmt->execute([$device_id, $model, $owner, $rental_end, $model, $owner, $rental_end]);
        }
    }

    if (isset($_POST['send_app_command'])) {
        $device_id = sanitize_input($_POST['target_device_id']);
        $package_name = sanitize_input($_POST['package_name']);
        $action = $_POST['app_action']; // hide_app or show_app

        if (in_array($action, ['hide_app', 'show_app'])) {
            $stmt = $pdo->prepare("INSERT INTO commands (device_id, command, payload) VALUES (?, ?, ?)");
            $stmt->execute([$device_id, $action, $package_name]);
        }
    }

    if (isset($_POST['bulk_action'])) {
        $action = $_POST['bulk_action'];
        if ($action == 'lock_all') {
            $pdo->exec("UPDATE devices SET is_locked = 1");
            $devices = $pdo->query("SELECT device_id FROM devices")->fetchAll();
            $stmt = $pdo->prepare("INSERT INTO commands (device_id, command) VALUES (?, 'lock')");
            foreach ($devices as $d) {
                $stmt->execute([$d['device_id']]);
            }
        } elseif ($action == 'unlock_all') {
            $pdo->exec("UPDATE devices SET is_locked = 0");
            $devices = $pdo->query("SELECT device_id FROM devices")->fetchAll();
            $stmt = $pdo->prepare("INSERT INTO commands (device_id, command) VALUES (?, 'unlock')");
            foreach ($devices as $d) {
                $stmt->execute([$d['device_id']]);
            }
        }
    }
}

if (isset($_GET['lock'])) {
    $id = (int)$_GET['lock'];
    $stmt = $pdo->prepare("SELECT device_id FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    $device = $stmt->fetch();
    if ($device) {
        $stmt = $pdo->prepare("UPDATE devices SET is_locked = 1 WHERE id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("INSERT INTO commands (device_id, command) VALUES (?, 'lock')");
        $stmt->execute([$device['device_id']]);
    }
    header("Location: devices.php");
    exit;
}

if (isset($_GET['unlock'])) {
    $id = (int)$_GET['unlock'];
    $stmt = $pdo->prepare("SELECT device_id FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    $device = $stmt->fetch();
    if ($device) {
        $stmt = $pdo->prepare("UPDATE devices SET is_locked = 0 WHERE id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("INSERT INTO commands (device_id, command) VALUES (?, 'unlock')");
        $stmt->execute([$device['device_id']]);
    }
    header("Location: devices.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: devices.php");
    exit;
}

$devices = $pdo->query("SELECT * FROM devices ORDER BY last_seen DESC")->fetchAll();

// Fetch apps for all devices to use in the modal (or fetch via AJAX when modal opens)
// For simplicity, we'll fetch via AJAX or just have it in a data attribute
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Devices - Phone Rental System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-inline { display: flex; gap: 1rem; align-items: flex-end; }
        .form-group { flex: 1; }
        .form-group label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem; }
        .input-text { width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.375rem; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header>
            <div class="container header-content">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
                    <h2 style="margin-bottom: 0;">Device Management</h2>
                </div>
                <button onclick="location.reload()" class="btn refresh-btn">Refresh</button>
            </div>
        </header>

        <main class="container">
            <?php if (isset($error)): ?>
                <div style="background: var(--danger); color: white; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>Add / Update Device</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <div class="form-inline">
                        <div class="form-group">
                            <label>Device ID (IMEI/Serial)</label>
                            <input type="text" name="device_id" class="input-text" required placeholder="Ex: 8642...">
                        </div>
                        <div class="form-group">
                            <label>Model Name</label>
                            <input type="text" name="model" class="input-text" placeholder="Ex: Pixel 7">
                        </div>
                        <div class="form-group">
                            <label>Renter Name</label>
                            <input type="text" name="owner_name" class="input-text" placeholder="Ex: John Doe">
                        </div>
                        <div class="form-group">
                            <label>Rental End Time</label>
                            <input type="datetime-local" name="rental_end" class="input-text">
                        </div>
                        <button type="submit" name="add_device" class="btn btn-primary" style="height: 38px;">Save Device</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <h2>Device List</h2>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <button type="submit" name="bulk_action" value="lock_all" class="btn btn-danger btn-sm" onclick="return confirm('Lock all devices?')">Lock All</button>
                            <button type="submit" name="bulk_action" value="unlock_all" class="btn btn-primary btn-sm" onclick="return confirm('Unlock all devices?')">Unlock All</button>
                        </form>
                        <input type="text" id="deviceSearch" placeholder="Search devices..." class="input-text" style="max-width: 200px;">
                    </div>
                </div>
                <table id="devicesTable">
                    <thead>
                        <tr>
                            <th>Device ID</th>
                            <th>Model</th>
                            <th>Owner</th>
                            <th>IP / Battery</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($device['device_id']); ?></code></td>
                            <td>
                                <?php if (is_online($device['last_seen'])): ?>
                                    <span style="display: inline-block; width: 10px; height: 10px; background: var(--success); border-radius: 50%; margin-right: 5px;" title="Online"></span>
                                <?php else: ?>
                                    <span style="display: inline-block; width: 10px; height: 10px; background: #cbd5e1; border-radius: 50%; margin-right: 5px;" title="Offline"></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($device['model']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($device['owner_name']); ?></td>
                            <td>
                                <small><?php echo htmlspecialchars($device['ip_address'] ?? 'Unknown'); ?></small>
                                <?php if (isset($device['battery_level'])): ?>
                                    <div style="display: flex; align-items: center; gap: 4px; margin-top: 4px;">
                                        <div style="width: 24px; height: 12px; border: 1px solid #64748b; border-radius: 2px; position: relative; padding: 1px;">
                                            <div style="width: <?php echo $device['battery_level']; ?>%; height: 100%; background: <?php echo $device['battery_level'] > 20 ? 'var(--success)' : 'var(--danger)'; ?>;"></div>
                                            <div style="position: absolute; right: -3px; top: 3px; width: 2px; height: 4px; background: #64748b;"></div>
                                        </div>
                                        <small><?php echo $device['battery_level']; ?>%<?php echo $device['is_charging'] ? ' ⚡' : ''; ?></small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $device['is_locked'] ? 'status-inactive' : 'status-active'; ?>">
                                    <?php echo $device['is_locked'] ? 'LOCKED' : 'ACTIVE'; ?>
                                </span>
                            </td>
                            <td><span class="last-seen"><?php echo time_elapsed_string($device['last_seen']); ?></span></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($device['is_locked']): ?>
                                        <a href="?unlock=<?php echo $device['id']; ?>" class="btn btn-primary btn-sm">Unlock</a>
                                    <?php else: ?>
                                        <a href="?lock=<?php echo $device['id']; ?>" class="btn btn-danger btn-sm">Lock</a>
                                    <?php endif; ?>
                                    <button onclick="openAppControl('<?php echo $device['device_id']; ?>')" class="btn btn-primary btn-sm" style="background: var(--warning);">Apps</button>
                                    <a href="?delete=<?php echo $device['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this device?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- App Control Modal (Simulated for brevity) -->
    <div id="appModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center;">
        <div class="card modal-content" style="width: 400px; margin-bottom: 0;">
            <h2 id="modalTitle">App Control</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="hidden" name="target_device_id" id="modalDeviceId">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>Device App Inventory</label>
                    <div id="appInventoryContainer"></div>

                    <label>Package Name</label>
                    <input type="text" name="package_name" id="packageNameInput" class="input-text" required placeholder="com.example.app">
                    <div class="suggestions">
                        <small>Common:</small>
                        <span class="suggestion-tag" onclick="setPackage('com.android.chrome')">Chrome</span>
                        <span class="suggestion-tag" onclick="setPackage('com.google.android.youtube')">YouTube</span>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Action</label>
                    <select name="app_action" class="input-text">
                        <option value="hide_app">Hide Application</option>
                        <option value="show_app">Show Application</option>
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" onclick="closeAppControl()" class="btn">Cancel</button>
                    <button type="submit" name="send_app_command" class="btn btn-primary">Send Command</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function fetchAppInventory(deviceId) {
            const container = document.getElementById('appInventoryContainer');
            container.innerHTML = 'Loading apps...';
            try {
                const response = await fetch('api/get_device_apps.php?device_id=' + deviceId);
                const apps = await response.json();
                if (apps && apps.length > 0) {
                    let html = '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 5px; margin-bottom: 10px; border-radius: 4px;">';
                    apps.forEach(app => {
                        html += `<div style="display: flex; justify-content: space-between; align-items: center; padding: 5px; border-bottom: 1px solid #f9f9f9; font-size: 0.85rem;">
                                    <span>${app.app_name} <br><small style="color: #888">${app.package_name}</small></span>
                                    <button type="button" class="suggestion-tag" onclick="setPackage('${app.package_name}')">Select</button>
                                 </div>`;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = 'No app data available for this device.';
                }
            } catch (e) {
                container.innerHTML = 'Error loading apps.';
            }
        }

        document.getElementById('deviceSearch').addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#devicesTable tbody tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });

        function openAppControl(deviceId) {
            document.getElementById('modalDeviceId').value = deviceId;
            document.getElementById('modalTitle').innerText = 'App Control: ' + deviceId;
            document.getElementById('appModal').style.display = 'flex';
            fetchAppInventory(deviceId);
        }
        function closeAppControl() {
            document.getElementById('appModal').style.display = 'none';
        }
        function setPackage(pkg) {
            document.getElementById('packageNameInput').value = pkg;
        }
    </script>
</body>
</html>
