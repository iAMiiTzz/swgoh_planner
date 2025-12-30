<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';

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
        'username' => $row['username'],
        'ally_codes' => $allyCodes
    ];
}

function formatAllyCode($code) {
    if (strlen($code) === 9) {
        return substr($code, 0, 3) . '-' . substr($code, 3, 3) . '-' . substr($code, 6);
    }
    return $code;
}
?>
<div class="guild-container">
    <h2>Guild Planner</h2>
    <div class="guild-users-list">
        <table class="guild-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Ally Codes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td>
                        <?php if (empty($user['ally_codes'])): ?>
                            <span class="no-ally-code">No ally codes</span>
                        <?php else: ?>
                            <?php foreach ($user['ally_codes'] as $code): ?>
                                <span class="ally-code-badge"><?php echo formatAllyCode($code); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

