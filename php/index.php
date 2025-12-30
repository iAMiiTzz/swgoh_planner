<?php
require_once 'config/auth.php';
require_once 'config/base_url.php';

if (isAuthenticated()) {
    redirect('/homepage.php');
} else {
    redirect('/login.php');
}
?>

