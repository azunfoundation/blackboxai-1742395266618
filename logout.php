<?php
require_once 'functions.php';

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Clear browser cache for sensitive pages
header("Clear-Site-Data: \"cache\", \"cookies\", \"storage\"");
setNoCacheHeaders();

// Redirect to login page
header("Location: login.php");
exit();
?>