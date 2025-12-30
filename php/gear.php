<?php
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';
?>
<div class="gear-container">
    <h2>Gear/Relic Planner</h2>
    <p style="color: #718096; margin-bottom: 20px;">Plan your gear and relic farming priorities.</p>
    <div class="card">
        <p>Gear Planner interface will be available here. The backend API is fully functional at <code>api/gear.php</code></p>
        <p>You can use the API endpoints to manage your gear farming data.</p>
    </div>
</div>
<script src="assets/js/gear.js"></script>
<?php require_once 'includes/footer.php'; ?>

