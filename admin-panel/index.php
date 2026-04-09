<?php
session_start();
require_once 'includes/db.php';

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
    <header>
        <div class="container header-content">
            <h1 style="font-size: 1.5rem; margin-bottom: 0;">Rental Admin</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="devices.php">Devices</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="grid">
            <div class="card">
                <h3>Total Devices</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);"><?php echo $total_devices; ?></p>
            </div>
            <div class="card">
                <h3>Active Rentals</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--success);"><?php echo $active_rentals; ?></p>
            </div>
            <div class="card">
                <h3>Locked Devices</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--danger);"><?php echo $locked_devices; ?></p>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2>Recent Devices</h2>
                <a href="devices.php" class="btn btn-primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Device ID</th>
                        <th>Model</th>
                        <th>Owner</th>
                        <th>Rental Ends</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($device['device_id']); ?></td>
                        <td><?php echo htmlspecialchars($device['model']); ?></td>
                        <td><?php echo htmlspecialchars($device['owner_name']); ?></td>
                        <td><?php echo $device['rental_end_time'] ?: 'N/A'; ?></td>
                        <td>
                            <span class="status-badge <?php echo $device['is_locked'] ? 'status-inactive' : 'status-active'; ?>">
                                <?php echo $device['is_locked'] ? 'LOCKED' : 'ACTIVE'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($devices)): ?>
                    <tr><td colspan="5" style="text-align: center;">No devices found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
