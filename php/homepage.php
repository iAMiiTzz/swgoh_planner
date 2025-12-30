<?php
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';
?>
<div class="homepage-container">
    <h1>Welcome to SWGOH Planner</h1>
    <div class="homepage-grid">
        <a href="gac.php" class="homepage-card">
            <h2>🛡️ GAC Planner</h2>
            <p>Plan your Grand Arena Championship defense and offense teams</p>
        </a>
        <a href="guild.php" class="homepage-card">
            <h2>👥 Guild Planner</h2>
            <p>View guild members and their ally codes</p>
        </a>
        <a href="journey.php" class="homepage-card">
            <h2>🌟 Journey Tracker</h2>
            <p>Track your character journey progress</p>
        </a>
        <a href="roster.php" class="homepage-card">
            <h2>📊 Roster Planner</h2>
            <p>Manage your character roster and stats</p>
        </a>
        <a href="gear.php" class="homepage-card">
            <h2>⚙️ Gear/Relic Planner</h2>
            <p>Plan your gear and relic farming</p>
        </a>
        <a href="settings.php" class="homepage-card">
            <h2>⚙️ Settings</h2>
            <p>Manage your account settings</p>
        </a>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

