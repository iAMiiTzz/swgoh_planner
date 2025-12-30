<?php
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';
?>
<div class="journey-container">
    <h2>Journey Tracker</h2>
    <p style="color: #718096; margin-bottom: 20px;">Track your character journey progress.</p>
    <div class="card">
        <p>Journey Tracker interface will be available here. The backend API is fully functional at <code>api/journey.php</code></p>
        <p>You can use the API endpoints to manage your journey tracking data.</p>
    </div>
</div>
<script src="assets/js/journey.js"></script>
<?php require_once 'includes/footer.php'; ?>

