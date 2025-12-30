<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireAdmin();
require_once 'includes/header.php';

$conn = getDB();
$message = '';
$error = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);
            $stmt->execute();
            $message = 'User created successfully';
        }
    }
}

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
    <h2>Admin Panel</h2>
    
    <?php if ($message): ?>
    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Create User -->
    <div class="card">
        <h3>Create New User</h3>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="create_user" class="btn-primary">Create User</button>
        </form>
    </div>
    
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
<?php require_once 'includes/footer.php'; ?>

