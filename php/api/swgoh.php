<?php
require_once '../config/auth.php';

header('Content-Type: application/json');
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$SWGOH_API_KEY = '3a8ac';
$SWGOH_API_BASE = 'https://swgoh.gg/api';

if ($method === 'GET' && $action === 'units') {
    $id = $_GET['id'] ?? null;
    
    $url = $id ? "$SWGOH_API_BASE/units/$id/" : "$SWGOH_API_BASE/units/";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-gg-bot-access: $SWGOH_API_KEY"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        header('Content-Type: application/json');
        echo $response;
    } else {
        jsonResponse(['error' => 'Error fetching units from SWGOH.gg API'], $httpCode ?: 500);
    }
    exit;
}

jsonResponse(['error' => 'Invalid action'], 400);
?>

