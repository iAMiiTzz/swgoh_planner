<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$conn = getDB();

switch ($method) {
    case 'POST':
        if ($action === 'login') {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = isset($data['username']) ? $data['username'] : '';
            $password = isset($data['password']) ? $data['password'] : '';
            
            if (empty($username) || empty($password)) {
                jsonResponse(['error' => 'Username and password are required'], 400);
            }
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                jsonResponse(['error' => 'Invalid credentials'], 401);
            }
            
            $user = $result->fetch_assoc();
            
            if (!password_verify($password, $user['password'])) {
                jsonResponse(['error' => 'Invalid credentials'], 401);
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'user';
            
            jsonResponse([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => isset($user['role']) ? $user['role'] : 'user'
                ]
            ]);
        }
        
        elseif ($action === 'change-password') {
            requireAuth();
            $data = json_decode(file_get_contents('php://input'), true);
            $currentPassword = isset($data['currentPassword']) ? $data['currentPassword'] : '';
            $newPassword = isset($data['newPassword']) ? $data['newPassword'] : '';
            
            if (empty($currentPassword) || empty($newPassword)) {
                jsonResponse(['error' => 'Current password and new password are required'], 400);
            }
            
            if (strlen($newPassword) < 6) {
                jsonResponse(['error' => 'New password must be at least 6 characters long'], 400);
            }
            
            $userId = getUserId();
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!password_verify($currentPassword, $user['password'])) {
                jsonResponse(['error' => 'Current password is incorrect'], 401);
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            $stmt->execute();
            
            jsonResponse(['message' => 'Password changed successfully']);
        }
        
        elseif ($action === 'change-username') {
            requireAuth();
            $data = json_decode(file_get_contents('php://input'), true);
            $newUsername = isset($data['newUsername']) ? $data['newUsername'] : '';
            $password = isset($data['password']) ? $data['password'] : '';
            
            if (empty($newUsername) || empty($password)) {
                jsonResponse(['error' => 'New username and password are required'], 400);
            }
            
            if (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
                jsonResponse(['error' => 'Username must be between 3 and 50 characters'], 400);
            }
            
            $userId = getUserId();
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!password_verify($password, $user['password'])) {
                jsonResponse(['error' => 'Password is incorrect'], 401);
            }
            
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $newUsername, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                jsonResponse(['error' => 'Username already exists'], 400);
            }
            
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $newUsername, $userId);
            $stmt->execute();
            
            $_SESSION['username'] = $newUsername;
            
            jsonResponse([
                'message' => 'Username changed successfully',
                'user' => [
                    'id' => $userId,
                    'username' => $newUsername,
                    'role' => getUserRole()
                ]
            ]);
        }
        
        elseif ($action === 'update-ally-codes') {
            requireAuth();
            $data = json_decode(file_get_contents('php://input'), true);
            $main = preg_replace('/[^0-9]/', '', isset($data['main_ally_code']) ? $data['main_ally_code'] : '');
            $alt = preg_replace('/[^0-9]/', '', isset($data['alt_ally_code']) ? $data['alt_ally_code'] : '');
            $extra = preg_replace('/[^0-9]/', '', isset($data['extra_ally_code']) ? $data['extra_ally_code'] : '');
            
            $validateAllyCode = function($code) {
                if (empty($code)) return true;
                return strlen($code) === 9 && ctype_digit($code);
            };
            
            if (!empty($main) && !$validateAllyCode($main)) {
                jsonResponse(['error' => 'Main ally code must be 9 digits'], 400);
            }
            if (!empty($alt) && !$validateAllyCode($alt)) {
                jsonResponse(['error' => 'Alt ally code must be 9 digits'], 400);
            }
            if (!empty($extra) && !$validateAllyCode($extra)) {
                jsonResponse(['error' => 'Extra ally code must be 9 digits'], 400);
            }
            
            $main = empty($main) ? null : $main;
            $alt = empty($alt) ? null : $alt;
            $extra = empty($extra) ? null : $extra;
            
            $userId = getUserId();
            $stmt = $conn->prepare("UPDATE users SET main_ally_code = ?, alt_ally_code = ?, extra_ally_code = ? WHERE id = ?");
            $stmt->bind_param("sssi", $main, $alt, $extra, $userId);
            $stmt->execute();
            
            jsonResponse([
                'message' => 'Ally codes updated successfully',
                'ally_codes' => [
                    'main_ally_code' => isset($main) ? $main : '',
                    'alt_ally_code' => isset($alt) ? $alt : '',
                    'extra_ally_code' => isset($extra) ? $extra : ''
                ]
            ]);
        }
        
        break;
        
    case 'GET':
        if ($action === 'verify') {
            requireAuth();
            $userId = getUserId();
            $stmt = $conn->prepare("SELECT id, username, role, main_ally_code, alt_ally_code, extra_ally_code FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            jsonResponse([
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => isset($user['role']) ? $user['role'] : 'user',
                    'main_ally_code' => isset($user['main_ally_code']) ? $user['main_ally_code'] : '',
                    'alt_ally_code' => isset($user['alt_ally_code']) ? $user['alt_ally_code'] : '',
                    'extra_ally_code' => isset($user['extra_ally_code']) ? $user['extra_ally_code'] : ''
                ]
            ]);
        }
        break;
        
    case 'POST':
        if ($action === 'logout') {
            session_destroy();
            jsonResponse(['message' => 'Logged out successfully']);
        }
        break;
}

jsonResponse(['error' => 'Invalid action'], 400);
?>

