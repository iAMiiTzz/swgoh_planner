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
            $stmt = $conn->prepare("SELECT * FROM roster WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                jsonResponse(['error' => 'Character not found'], 404);
            }
            
            jsonResponse($result->fetch_assoc());
        } else {
            $stmt = $conn->prepare("SELECT * FROM roster WHERE user_id = ? ORDER BY character_name");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $characters = [];
            
            while ($row = $result->fetch_assoc()) {
                $characters[] = $row;
            }
            
            jsonResponse($characters);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $characterName = isset($data['character_name']) ? $data['character_name'] : '';
        
        if (empty($characterName)) {
            jsonResponse(['error' => 'Character name is required'], 400);
        }
        
        $starLevel = isset($data['star_level']) ? $data['star_level'] : 1;
        $gearLevel = isset($data['gear_level']) ? $data['gear_level'] : 1;
        $relicLevel = isset($data['relic_level']) ? $data['relic_level'] : 0;
        $zetaCount = isset($data['zeta_count']) ? $data['zeta_count'] : 0;
        $omicronCount = isset($data['omicron_count']) ? $data['omicron_count'] : 0;
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        // Check if character exists
        $stmt = $conn->prepare("SELECT id FROM roster WHERE user_id = ? AND character_name = ?");
        $stmt->bind_param("is", $userId, $characterName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing
            $row = $result->fetch_assoc();
            $stmt = $conn->prepare("UPDATE roster SET star_level = ?, gear_level = ?, relic_level = ?, zeta_count = ?, omicron_count = ?, notes = ? WHERE id = ?");
            $stmt->bind_param("iiiiiis", $starLevel, $gearLevel, $relicLevel, $zetaCount, $omicronCount, $notes, $row['id']);
            $stmt->execute();
            jsonResponse(['id' => $row['id'], 'message' => 'Character updated successfully']);
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO roster (user_id, character_name, star_level, gear_level, relic_level, zeta_count, omicron_count, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiiiiis", $userId, $characterName, $starLevel, $gearLevel, $relicLevel, $zetaCount, $omicronCount, $notes);
            $stmt->execute();
            jsonResponse(['id' => $conn->insert_id, 'message' => 'Character created successfully'], 201);
        }
        break;
        
    case 'PUT':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'Character ID is required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $characterName = isset($data['character_name']) ? $data['character_name'] : '';
        $starLevel = isset($data['star_level']) ? $data['star_level'] : 1;
        $gearLevel = isset($data['gear_level']) ? $data['gear_level'] : 1;
        $relicLevel = isset($data['relic_level']) ? $data['relic_level'] : 0;
        $zetaCount = isset($data['zeta_count']) ? $data['zeta_count'] : 0;
        $omicronCount = isset($data['omicron_count']) ? $data['omicron_count'] : 0;
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $stmt = $conn->prepare("UPDATE roster SET character_name = ?, star_level = ?, gear_level = ?, relic_level = ?, zeta_count = ?, omicron_count = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("siiiiiisii", $characterName, $starLevel, $gearLevel, $relicLevel, $zetaCount, $omicronCount, $notes, $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'Character not found'], 404);
        }
        
        jsonResponse(['message' => 'Character updated successfully']);
        break;
        
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'Character ID is required'], 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM roster WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'Character not found'], 404);
        }
        
        jsonResponse(['message' => 'Character deleted successfully']);
        break;
}

jsonResponse(['error' => 'Invalid method'], 405);
?>

