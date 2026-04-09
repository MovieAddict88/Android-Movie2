<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch stats
$total_devices = $pdo->query("SELECT COUNT(*) FROM devices")->fetchColumn();
$active_rentals = $pdo->query("SELECT COUNT(*) FROM devices WHERE rental_end_time > NOW()")->fetchColumn();
$locked_devices = $pdo->query("SELECT COUNT(*) FROM devices WHERE is_locked = 1")->fetchColumn();

// Fetch devices
$devices = $pdo->query("SELECT * FROM devices ORDER BY last_seen DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Phone Rental System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <aside class="sidebar">
        <h1>Rental Admin</h1>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="devices.php">Devices</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header>
            <div class="container header-content">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
                    <h2 style="margin-bottom: 0;">Dashboard Overview</h2>
                </div>
                <div class="user-info" style="display: flex; gap: 1rem; align-items: center;">
                    <button onclick="location.reload()" class="btn refresh-btn">Refresh</button>
                    <span>Admin</span>
                </div>
            </div>
        </header>

        <main class="container">
            <div class="grid">
                <div class="card">
                    <h3>Total Devices</h3>
                    <p style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color);"><?php echo $total_devices; ?></p>
                </div>
                <div class="card">
                    <h3>Active Rentals</h3>
                    <p style="font-size: 2.5rem; font-weight: bold; color: var(--success);"><?php echo $active_rentals; ?></p>
                </div>
                <div class="card">
                    <h3>Locked Devices</h3>
                    <p style="font-size: 2.5rem; font-weight: bold; color: var(--danger);"><?php echo $locked_devices; ?></p>
                </div>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2>Recent Activity</h2>
                    <a href="devices.php" class="btn btn-primary">View All Devices</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Device ID</th>
                            <th>Model</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Last Seen</th>
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
                                <span class="status-badge <?php echo $device['is_locked'] ? 'status-inactive' : 'status-active'; ?>">
                                    <?php echo $device['is_locked'] ? 'LOCKED' : 'ACTIVE'; ?>
                                </span>
                            </td>
                            <td><span class="last-seen"><?php echo time_elapsed_string($device['last_seen']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($devices)): ?>
                        <tr><td colspan="5" style="text-align: center;">No devices found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
