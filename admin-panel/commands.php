<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch command history
$stmt = $pdo->prepare("SELECT c.*, d.model, d.owner_name FROM commands c LEFT JOIN devices d ON c.device_id = d.device_id ORDER BY c.created_at DESC LIMIT 50");
$stmt->execute();
$commands = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command History - Phone Rental System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header>
            <div class="container header-content">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
                    <h2 style="margin-bottom: 0;">Command History</h2>
                </div>
                <button onclick="location.reload()" class="btn refresh-btn">Refresh</button>
            </div>
        </header>

        <main class="container">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2>Last 50 Commands</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Device</th>
                            <th>Command</th>
                            <th>Payload</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commands as $cmd): ?>
                        <tr>
                            <td><span class="last-seen"><?php echo $cmd['created_at']; ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($cmd['model'] ?? 'Unknown'); ?></strong><br>
                                <small><code><?php echo htmlspecialchars($cmd['device_id']); ?></code></small>
                            </td>
                            <td><code><?php echo htmlspecialchars($cmd['command']); ?></code></td>
                            <td><small><?php echo htmlspecialchars($cmd['payload'] ?? '-'); ?></small></td>
                            <td>
                                <span class="status-badge <?php echo $cmd['status'] == 'executed' ? 'status-active' : 'status-warning'; ?>" style="<?php echo $cmd['status'] == 'pending' ? 'background: #fef9c3; color: #854d0e;' : ''; ?>">
                                    <?php echo strtoupper($cmd['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($commands)): ?>
                        <tr><td colspan="5" style="text-align: center;">No commands found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
