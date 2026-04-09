<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <h1>Rental Admin</h1>
    <nav>
        <ul>
            <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="devices.php" class="<?php echo $current_page == 'devices.php' ? 'active' : ''; ?>">Devices</a></li>
            <li><a href="commands.php" class="<?php echo $current_page == 'commands.php' ? 'active' : ''; ?>">Commands</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</aside>
