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
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM journey_tracker WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                jsonResponse(['error' => 'Journey not found'], 404);
            }
            
            jsonResponse($result->fetch_assoc());
        } else {
            $stmt = $conn->prepare("SELECT * FROM journey_tracker WHERE user_id = ? ORDER BY journey_name, character_name");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $journeys = [];
            
            while ($row = $result->fetch_assoc()) {
                $journeys[] = $row;
            }
            
            jsonResponse($journeys);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $journeyName = $data['journey_name'] ?? '';
        $characterName = $data['character_name'] ?? '';
        $totalStages = $data['total_stages'] ?? 0;
        
        if (empty($journeyName) || empty($characterName) || empty($totalStages)) {
            jsonResponse(['error' => 'Journey name, character name, and total stages are required'], 400);
        }
        
        $currentStage = $data['current_stage'] ?? 0;
        $unlocked = isset($data['unlocked']) ? (int)$data['unlocked'] : 0;
        $notes = $data['notes'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO journey_tracker (user_id, journey_name, character_name, current_stage, total_stages, unlocked, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiiis", $userId, $journeyName, $characterName, $currentStage, $totalStages, $unlocked, $notes);
        $stmt->execute();
        
        jsonResponse(['id' => $conn->insert_id, 'message' => 'Journey tracker created successfully'], 201);
        break;
        
    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            jsonResponse(['error' => 'Journey ID is required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $journeyName = $data['journey_name'] ?? '';
        $characterName = $data['character_name'] ?? '';
        $currentStage = $data['current_stage'] ?? 0;
        $totalStages = $data['total_stages'] ?? 0;
        $unlocked = isset($data['unlocked']) ? (int)$data['unlocked'] : 0;
        $notes = $data['notes'] ?? '';
        
        $stmt = $conn->prepare("UPDATE journey_tracker SET journey_name = ?, character_name = ?, current_stage = ?, total_stages = ?, unlocked = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssiiisii", $journeyName, $characterName, $currentStage, $totalStages, $unlocked, $notes, $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'Journey not found'], 404);
        }
        
        jsonResponse(['message' => 'Journey updated successfully']);
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            jsonResponse(['error' => 'Journey ID is required'], 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM journey_tracker WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'Journey not found'], 404);
        }
        
        jsonResponse(['message' => 'Journey deleted successfully']);
        break;
}

jsonResponse(['error' => 'Invalid method'], 405);
