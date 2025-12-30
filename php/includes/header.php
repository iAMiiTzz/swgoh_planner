<?php
require_once '../config/auth.php';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isAuthenticated = isAuthenticated();
$userRole = getUserRole();
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWGOH Planner<?php echo $isAuthenticated ? ' - ' . ucfirst($currentPage) : ''; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/<?php echo $currentPage; ?>.css">
</head>
<body>
<?php if ($isAuthenticated): ?>
<nav class="dashboard-nav">
    <div class="nav-brand">
        <h1>SWGOH Planner</h1>
    </div>
    <div class="nav-links">
        <a href="homepage.php" class="<?php echo $currentPage === 'homepage' ? 'active' : ''; ?>">Homepage</a>
        <a href="gac.php" class="<?php echo $currentPage === 'gac' ? 'active' : ''; ?>">GAC Planner</a>
        <a href="guild.php" class="<?php echo $currentPage === 'guild' ? 'active' : ''; ?>">Guild Planner</a>
        <a href="journey.php" class="<?php echo $currentPage === 'journey' ? 'active' : ''; ?>">Journey Tracker</a>
        <a href="roster.php" class="<?php echo $currentPage === 'roster' ? 'active' : ''; ?>">Roster Planner</a>
        <a href="gear.php" class="<?php echo $currentPage === 'gear' ? 'active' : ''; ?>">Gear/Relic Planner</a>
        <a href="settings.php" class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">Settings</a>
        <?php if ($isAdmin): ?>
        <a href="admin.php" class="<?php echo $currentPage === 'admin' ? 'active' : ''; ?>">Admin</a>
        <?php endif; ?>
    </div>
    <button onclick="logout()" class="logout-button">Logout</button>
</nav>
<main class="dashboard-content">
<?php endif; ?>

