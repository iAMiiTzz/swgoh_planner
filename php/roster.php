<?php
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';
?>
<div class="roster-container">
    <h2>Roster Planner</h2>
    <p style="color: #718096; margin-bottom: 20px;">Manage your character roster and stats.</p>
    <div class="card">
        <p>Roster Planner interface will be available here. The backend API is fully functional at <code>api/roster.php</code></p>
        <p>You can use the API endpoints to manage your roster data.</p>
    </div>
</div>
<script src="assets/js/roster.js"></script>
<?php require_once 'includes/footer.php'; ?>

