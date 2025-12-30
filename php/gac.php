<?php
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';
?>
<div class="gac-container">
    <h2>GAC Planner</h2>
    <p style="color: #718096; margin-bottom: 20px;">Note: Full GAC planner with character selection modal coming soon. Basic functionality available via API.</p>
    <div class="card">
        <p>GAC Planner interface will be available here. The backend API is fully functional at <code>api/gac.php</code></p>
        <p>You can use the API endpoints to create, read, update, and delete GAC plans.</p>
    </div>
</div>
<script src="assets/js/gac.js"></script>
<?php require_once 'includes/footer.php'; ?>

