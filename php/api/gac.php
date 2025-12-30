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
            $stmt = $conn->prepare("SELECT * FROM gac_plans WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                jsonResponse(['error' => 'GAC plan not found'], 404);
            }
            
            $plan = $result->fetch_assoc();
            $plan['defense_teams'] = json_decode(isset($plan['defense_teams']) ? $plan['defense_teams'] : '[]', true);
            $plan['offense_teams'] = json_decode(isset($plan['offense_teams']) ? $plan['offense_teams'] : '[]', true);
            $plan['fleet_teams'] = json_decode(isset($plan['fleet_teams']) ? $plan['fleet_teams'] : '[]', true);
            jsonResponse($plan);
        } else {
            $stmt = $conn->prepare("SELECT * FROM gac_plans WHERE user_id = ? ORDER BY updated_at DESC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $plans = [];
            
            while ($row = $result->fetch_assoc()) {
                $row['defense_teams'] = json_decode(isset($row['defense_teams']) ? $row['defense_teams'] : '[]', true);
                $row['offense_teams'] = json_decode(isset($row['offense_teams']) ? $row['offense_teams'] : '[]', true);
                $row['fleet_teams'] = json_decode(isset($row['fleet_teams']) ? $row['fleet_teams'] : '[]', true);
                $plans[] = $row;
            }
            
            jsonResponse($plans);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $planName = isset($data['plan_name']) ? $data['plan_name'] : '';
        
        if (empty($planName)) {
            jsonResponse(['error' => 'Plan name is required'], 400);
        }
        
        $league = isset($data['league']) ? $data['league'] : 'kyber';
        $format = isset($data['format']) ? $data['format'] : '5v5';
        $defenseTeams = json_encode(isset($data['defense_teams']) ? $data['defense_teams'] : []);
        $offenseTeams = json_encode(isset($data['offense_teams']) ? $data['offense_teams'] : []);
        $fleetTeams = json_encode(isset($data['fleet_teams']) ? $data['fleet_teams'] : []);
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $stmt = $conn->prepare("INSERT INTO gac_plans (user_id, plan_name, league, format, defense_teams, offense_teams, fleet_teams, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $userId, $planName, $league, $format, $defenseTeams, $offenseTeams, $fleetTeams, $notes);
        $stmt->execute();
        
        jsonResponse(['id' => $conn->insert_id, 'message' => 'GAC plan created successfully'], 201);
        break;
        
    case 'PUT':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'Plan ID is required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $planName = isset($data['plan_name']) ? $data['plan_name'] : '';
        $league = isset($data['league']) ? $data['league'] : 'kyber';
        $format = isset($data['format']) ? $data['format'] : '5v5';
        $defenseTeams = json_encode(isset($data['defense_teams']) ? $data['defense_teams'] : []);
        $offenseTeams = json_encode(isset($data['offense_teams']) ? $data['offense_teams'] : []);
        $fleetTeams = json_encode(isset($data['fleet_teams']) ? $data['fleet_teams'] : []);
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $stmt = $conn->prepare("UPDATE gac_plans SET plan_name = ?, league = ?, format = ?, defense_teams = ?, offense_teams = ?, fleet_teams = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssssssii", $planName, $league, $format, $defenseTeams, $offenseTeams, $fleetTeams, $notes, $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'GAC plan not found'], 404);
        }
        
        jsonResponse(['message' => 'GAC plan updated successfully']);
        break;
        
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'Plan ID is required'], 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM gac_plans WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            jsonResponse(['error' => 'GAC plan not found'], 404);
        }
        
        jsonResponse(['message' => 'GAC plan deleted successfully']);
        break;
}

jsonResponse(['error' => 'Invalid method'], 405);
