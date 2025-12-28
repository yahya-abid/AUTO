<?php
// logout.php
require_once 'includes/auth.php';

if (isset($auth)) {
    $auth->logout();
} else {
    session_start();
    session_destroy();
    setcookie('CAR_RENTAL_SESSION', '', time() - 3600, '/');
    header('Location: index.php');
    exit();
}
?>