<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function isEmployee() {
    return isLoggedIn() && $_SESSION['user_role'] === 'employee';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: ventas.php");
        exit();
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Usuario';
}
?>
