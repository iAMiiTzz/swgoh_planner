<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireAuth();

$conn = getDB();
$stmt = $conn->prepare("SELECT id, username, main_ally_code, alt_ally_code, extra_ally_code FROM users ORDER BY username");
$stmt->execute();
$result = $stmt->get_result();
$users = [];

while ($row = $result->fetch_assoc()) {
    $allyCodes = [];
    if (!empty($row['main_ally_code'])) $allyCodes[] = $row['main_ally_code'];
    if (!empty($row['alt_ally_code'])) $allyCodes[] = $row['alt_ally_code'];
    if (!empty($row['extra_ally_code'])) $allyCodes[] = $row['extra_ally_code'];
    
    $users[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'ally_codes' => $allyCodes
    ];
}

jsonResponse($users);
