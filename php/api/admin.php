<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDB();
$currentUserId = getUserId();

switch ($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'users') {
            $stmt = $conn->prepare("SELECT id, username, role, main_ally_code, alt_ally_code, extra_ally_code, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            $users = [];
            
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            jsonResponse($users);
        }
        break;
        
    case 'POST':
        if (isset($_GET['action']) && $_GET['action'] === 'users') {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = isset($data['username']) ? $data['username'] : '';
            $password = isset($data['password']) ? $data['password'] : '';
            $role = isset($data['role']) ? $data['role'] : 'user';
            
            if (empty($username) || empty($password)) {
                jsonResponse(['error' => 'Username and password are required'], 400);
            }
            
            if (strlen($password) < 6) {
                jsonResponse(['error' => 'Password must be at least 6 characters long'], 400);
            }
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                jsonResponse(['error' => 'Username already exists'], 400);
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);
            $stmt->execute();
            
            jsonResponse([
                'message' => 'User created successfully',
                'user' => ['id' => $conn->insert_id, 'username' => $username, 'role' => $role]
            ], 201);
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['action']) && $_GET['action'] === 'users') {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            
            if (!$id) {
                jsonResponse(['error' => 'User ID is required'], 400);
            }
            
            $id = (int)$id;
            
            // Prevent deleting yourself
            if ($id === $currentUserId) {
                jsonResponse(['error' => 'You cannot delete your own account'], 400);
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                jsonResponse(['error' => 'User not found'], 404);
            }
            
            jsonResponse(['message' => 'User deleted successfully']);
        }
        break;
}

jsonResponse(['error' => 'Invalid action'], 400);
?>

