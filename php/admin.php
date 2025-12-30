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
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <?php
                            $allyCodes = [];
                            if (!empty($user['main_ally_code'])) $allyCodes[] = formatAllyCode($user['main_ally_code']);
                            if (!empty($user['alt_ally_code'])) $allyCodes[] = formatAllyCode($user['alt_ally_code']);
                            if (!empty($user['extra_ally_code'])) $allyCodes[] = formatAllyCode($user['extra_ally_code']);
                            
                            if (!empty($allyCodes)) {
                                foreach ($allyCodes as $code) {
                                    echo '<span class="ally-code-badge">' . htmlspecialchars($code) . '</span>';
                                }
                            } else {
                                echo '<span style="color: #a0aec0;">None</span>';
                            }
                            ?>
                        </div>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <button onclick="openChangePasswordModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')" class="btn-secondary" style="padding: 6px 12px; font-size: 0.9rem;">Change Password</button>
                            <?php if ($user['id'] != getUserId()): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn-danger" style="padding: 6px 12px; font-size: 0.9rem;">Delete</button>
                            </form>
                            <?php else: ?>
                            <span style="color: #a0aec0; font-size: 0.9rem;">Current User</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button class="modal-close" onclick="closeChangePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="changePasswordForm">
                <input type="hidden" id="changePasswordUserId" name="user_id">
                <div class="form-group">
                    <label>User</label>
                    <input type="text" id="changePasswordUsername" readonly style="background-color: #f7fafc; cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="changePasswordNewPassword" name="password" required minlength="6" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="changePasswordConfirm" required minlength="6" placeholder="Confirm new password">
                </div>
                <div id="changePasswordError" class="error-message" style="display: none;"></div>
                <div class="modal-footer">
                    <button type="button" onclick="closeChangePasswordModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Change Password</button>
                </div>
            </form>
        </div>
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

// Change Password Modal Functions
function openChangePasswordModal(userId, username) {
    document.getElementById('changePasswordModal').style.display = 'flex';
    document.getElementById('changePasswordUserId').value = userId;
    document.getElementById('changePasswordUsername').value = username;
    document.getElementById('changePasswordForm').reset();
    document.getElementById('changePasswordUserId').value = userId;
    document.getElementById('changePasswordUsername').value = username;
    document.getElementById('changePasswordError').style.display = 'none';
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'none';
    document.getElementById('changePasswordForm').reset();
    document.getElementById('changePasswordError').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('changePasswordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeChangePasswordModal();
    }
});

// Handle password change form submission
document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const userId = document.getElementById('changePasswordUserId').value;
    const newPassword = document.getElementById('changePasswordNewPassword').value;
    const confirmPassword = document.getElementById('changePasswordConfirm').value;
    const errorDiv = document.getElementById('changePasswordError');
    
    errorDiv.style.display = 'none';
    
    // Validate passwords match
    if (newPassword !== confirmPassword) {
        errorDiv.textContent = 'Passwords do not match';
        errorDiv.style.display = 'block';
        return;
    }
    
    // Validate password length
    if (newPassword.length < 6) {
        errorDiv.textContent = 'Password must be at least 6 characters long';
        errorDiv.style.display = 'block';
        return;
    }
    
    try {
        const result = await api.admin.changePassword(userId, newPassword);
        showSuccess(result.message || 'Password changed successfully');
        closeChangePasswordModal();
    } catch (error) {
        errorDiv.textContent = error.message || 'Failed to change password';
        errorDiv.style.display = 'block';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

