<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDB();
$userId = getUserId();

switch ($method) {
    case 'GET':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM gear_farming WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                jsonResponse(['error' => 'Gear item not found'], 404);
            }
            
            jsonResponse($result->fetch_assoc());
        } else {
            $stmt = $conn->prepare("SELECT * FROM gear_farming WHERE user_id = ? ORDER BY priority DESC, created_at DESC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];
            
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            jsonResponse($items);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $gearName = isset($data['gear_name']) ? $data['gear_name'] : '';
        $targetQuantity = isset($data['target_quantity']) ? $data['target_quantity'] : 0;
        
        if (empty($gearName) || empty($targetQuantity)) {
            jsonResponse(['error' => 'Gear name and target quantity are required'], 400);
        }
        
        $characterName = isset($data['character_name']) ? $data['character_name'] : null;
        $gearType = isset($data['gear_type']) ? $data['gear_type'] : 'gear';
        $currentQuantity = isset($data['current_quantity']) ? $data['current_quantity'] : 0;
        $priority = isset($data['priority']) ? $data['priority'] : 5;
        $farmingLocation = isset($data['farming_location']) ? $data['farming_location'] : null;
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $stmt = $conn->prepare("INSERT INTO gear_farming (user_id, character_name, gear_name, gear_type, target_quantity, current_quantity, priority, farming_location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiiiss", $userId, $characterName, $gearName, $gearType, $targetQuantity, $currentQuantity, $priority, $farmingLocation, $notes);
        $stmt->execute();
        
        jsonResponse(['id' => $conn->insert_id, 'message' => 'Gear item created successfully'], 201);
        break;
        
    case 'PUT':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'Gear item ID is required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $characterName = isset($data['character_name']) ? $data['character_name'] : null;
        $gearName = isset($data['gear_name']) ? $data['gear_name'] : '';
        $gearType = isset($data['gear_type']) ? $data['gear_type'] : 'gear';
        $targetQuantity = isset($data['target_quantity']) ? $data['target_quantity'] : 0;
        $currentQuantity = isset($data['current_quantity']) ? $data['current_quantity'] : 0;
        $priority = isset($data['priority']) ? $data['priority'] : 5;
        $farmingLocation = isset($data['farming_location']) ? $data['farming_location'] : null;
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $stmt = $conn->prepare("UPDATE gear_farming SET character_name = ?, gear_name = ?, gear_type = ?, target_quantity = ?, current_quantity = ?, priority = ?, farming_location = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssiiissii", $characterName, $gearName, $gearType, $targetQuantity, $currentQuantity, $priority, $farmingLocation, $notes, $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'Gear item not found'], 404);
        }
        
        jsonResponse(['message' => 'Gear item updated successfully']);
        break;
        
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'Gear item ID is required'], 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM gear_farming WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'Gear item not found'], 404);
        }
        
        jsonResponse(['message' => 'Gear item deleted successfully']);
        break;
}

jsonResponse(['error' => 'Invalid method'], 405);
?>

