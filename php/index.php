<?php
require_once 'config/auth.php';

if (isAuthenticated()) {
    header('Location: /homepage.php');
} else {
    header('Location: /login.php');
}
exit;
?>

