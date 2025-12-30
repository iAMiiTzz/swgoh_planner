<?php
require_once 'config/auth.php';

// Redirect if already logged in
if (isAuthenticated()) {
    header('Location: /homepage.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $conn = require 'config/database.php';
        $conn = getDB();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                
                header('Location: /homepage.php');
                exit;
            }
        }
        
        $error = 'Invalid credentials';
    } else {
        $error = 'Username and password are required';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWGOH Planner - Login</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <h1>SWGOH Planner</h1>
        <h2>Login</h2>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Username</label>
                <input
                    type="text"
                    name="username"
                    required
                    placeholder="Enter your username"
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    placeholder="Enter your password"
                >
            </div>
            
            <button type="submit" class="submit-button">Login</button>
        </form>
    </div>
</div>
</body>
</html>

