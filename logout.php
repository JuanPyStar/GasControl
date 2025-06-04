<?php
include_once 'config/session.php';

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>