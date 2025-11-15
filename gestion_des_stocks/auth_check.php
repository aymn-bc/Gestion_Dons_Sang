<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check_authorization(array $allowed_roles) {
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role'])) {
        header('Location: login.php?error=not_logged_in');
        exit();
    }

    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: dashboard.php?error=unauthorized_access');
        exit();
    }
}
?>
