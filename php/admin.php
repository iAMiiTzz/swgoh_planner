<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireAdmin();
require_once 'includes/header.php';

$conn = getDB();
$message = '';
$error = '';

// Note: User creation is now handled via API (modal)

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $currentUserId = getUserId();
    
    if ($userId === $currentUserId) {
        $error = 'You cannot delete your own account';
    } elseif ($userId > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $message = 'User deleted successfully';
    }
}

// Get all users
$stmt = $conn->prepare("SELECT id, username, role, main_ally_code, alt_ally_code, extra_ally_code, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

function formatAllyCode($code) {
    if (empty($code)) return '';
    if (strlen($code) === 9) {
        return substr($code, 0, 3) . '-' . substr($code, 3, 3) . '-' . substr($code, 6);
    }
    return $code;
}
?>
<div class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Admin Panel</h2>
        <button onclick="openCreateUserModal()" class="btn-primary">+ Create New User</button>
    </div>
    
    <?php if ($message): ?>
    <div class="success-message" id="successMessage"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="error-message" id="errorMessage"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Users List -->
    <div class="card">
        <h3>All Users</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Ally Codes</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <?php
                        $allyCodes = [];
                        if (!empty($user['main_ally_code'])) $allyCodes[] = formatAllyCode($user['main_ally_code']);
                        if (!empty($user['alt_ally_code'])) $allyCodes[] = formatAllyCode($user['alt_ally_code']);
                        if (!empty($user['extra_ally_code'])) $allyCodes[] = formatAllyCode($user['extra_ally_code']);
                        echo !empty($allyCodes) ? implode(', ', $allyCodes) : 'None';
                        ?>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if ($user['id'] != getUserId()): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="delete_user" class="btn-danger">Delete</button>
                        </form>
                        <?php else: ?>
                        <span style="color: #a0aec0;">Current User</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New User</h3>
            <button class="modal-close" onclick="closeCreateUserModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createUserForm">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="newUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="newPassword" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="newRole" name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div id="createUserError" class="error-message" style="display: none;"></div>
                <div class="modal-footer">
                    <button type="button" onclick="closeCreateUserModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'flex';
    document.getElementById('createUserForm').reset();
    document.getElementById('createUserError').style.display = 'none';
}

function closeCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'none';
    document.getElementById('createUserForm').reset();
    document.getElementById('createUserError').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('createUserModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateUserModal();
    }
});

// Handle form submission
document.getElementById('createUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const username = document.getElementById('newUsername').value;
    const password = document.getElementById('newPassword').value;
    const role = document.getElementById('newRole').value;
    const errorDiv = document.getElementById('createUserError');
    
    errorDiv.style.display = 'none';
    
    try {
        const result = await api.admin.createUser({ username, password, role });
        showSuccess(result.message || 'User created successfully');
        closeCreateUserModal();
        // Reload page to show new user
        setTimeout(() => window.location.reload(), 1000);
    } catch (error) {
        errorDiv.textContent = error.message || 'Failed to create user';
        errorDiv.style.display = 'block';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

