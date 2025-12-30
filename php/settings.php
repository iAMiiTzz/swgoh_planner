<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';

$conn = getDB();
$userId = getUserId();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            $error = 'Both passwords are required';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                $stmt->execute();
                $message = 'Password changed successfully';
            } else {
                $error = 'Current password is incorrect';
            }
        }
    }
    
    if (isset($_POST['change_username'])) {
        $newUsername = $_POST['new_username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($newUsername) || empty($password)) {
            $error = 'New username and password are required';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Check if username exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->bind_param("si", $newUsername, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Username already exists';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $stmt->bind_param("si", $newUsername, $userId);
                    $stmt->execute();
                    $_SESSION['username'] = $newUsername;
                    $message = 'Username changed successfully';
                }
            } else {
                $error = 'Password is incorrect';
            }
        }
    }
    
    if (isset($_POST['update_ally_codes'])) {
        $main = preg_replace('/[^0-9]/', '', $_POST['main_ally_code'] ?? '');
        $alt = preg_replace('/[^0-9]/', '', $_POST['alt_ally_code'] ?? '');
        $extra = preg_replace('/[^0-9]/', '', $_POST['extra_ally_code'] ?? '');
        
        $main = empty($main) ? null : (strlen($main) === 9 ? $main : null);
        $alt = empty($alt) ? null : (strlen($alt) === 9 ? $alt : null);
        $extra = empty($extra) ? null : (strlen($extra) === 9 ? $extra : null);
        
        if ($main === null && !empty($_POST['main_ally_code'])) {
            $error = 'Main ally code must be 9 digits';
        } elseif ($alt === null && !empty($_POST['alt_ally_code'])) {
            $error = 'Alt ally code must be 9 digits';
        } elseif ($extra === null && !empty($_POST['extra_ally_code'])) {
            $error = 'Extra ally code must be 9 digits';
        } else {
            $stmt = $conn->prepare("UPDATE users SET main_ally_code = ?, alt_ally_code = ?, extra_ally_code = ? WHERE id = ?");
            $stmt->bind_param("sssi", $main, $alt, $extra, $userId);
            $stmt->execute();
            $message = 'Ally codes updated successfully';
        }
    }
}

// Get current user data
$stmt = $conn->prepare("SELECT username, main_ally_code, alt_ally_code, extra_ally_code FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

function formatAllyCode($code) {
    if (empty($code)) return '';
    if (strlen($code) === 9) {
        return substr($code, 0, 3) . '-' . substr($code, 3, 3) . '-' . substr($code, 6);
    }
    return $code;
}
?>
<div class="settings-container">
    <h2>Settings</h2>
    
    <?php if ($message): ?>
    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Ally Codes Section -->
    <div class="card">
        <h3>Ally Codes</h3>
        <form method="POST">
            <div class="form-group">
                <label>Main Account Ally Code</label>
                <input type="text" name="main_ally_code" value="<?php echo formatAllyCode($user['main_ally_code'] ?? ''); ?>" placeholder="123-456-789" maxlength="11">
            </div>
            <div class="form-group">
                <label>Alt Account Ally Code</label>
                <input type="text" name="alt_ally_code" value="<?php echo formatAllyCode($user['alt_ally_code'] ?? ''); ?>" placeholder="123-456-789" maxlength="11">
            </div>
            <div class="form-group">
                <label>Extra Ally Code</label>
                <input type="text" name="extra_ally_code" value="<?php echo formatAllyCode($user['extra_ally_code'] ?? ''); ?>" placeholder="123-456-789" maxlength="11">
            </div>
            <button type="submit" name="update_ally_codes" class="btn-primary">Update Ally Codes</button>
        </form>
    </div>
    
    <!-- Change Username -->
    <div class="card" style="float: right; width: 48%;">
        <h3>Change Username</h3>
        <form method="POST">
            <div class="form-group">
                <label>New Username</label>
                <input type="text" name="new_username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="change_username" class="btn-primary">Change Username</button>
        </form>
    </div>
    
    <!-- Change Password -->
    <div class="card" style="clear: both; margin-top: 20px;">
        <h3>Change Password</h3>
        <form method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <button type="submit" name="change_password" class="btn-primary">Change Password</button>
        </form>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

