<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'username', 'password', 'expense_tracker');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Session configuration
ini_set('session.cookie_lifetime', 86400); // 24 hours
ini_set('session.gc_maxlifetime', 86400); // 24 hours
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cache_limiter', 'nocache');

// Session management functions
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Check session expiry
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 86400)) {
        session_unset();
        session_destroy();
        return false;
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Clear all session data
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
        
        // Set error message
        $_SESSION['message'] = 'Please log in to continue';
        $_SESSION['message_type'] = 'error';
        
        // Redirect to login
        header('Location: login.php');
        exit();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration']) || 
        (time() - $_SESSION['last_regeneration']) > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Anti-CSRF token functions
function generateToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Cache control headers
function setNoCacheHeaders() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Past date
}

// Format currency
function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}

// Get total income
function getTotalIncome() {
    global $conn;
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT SUM(amount) as total FROM income WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get total expenses
function getTotalExpense() {
    global $conn;
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT SUM(amount) as total FROM expenses WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get balance
function getBalance() {
    return getTotalIncome() - getTotalExpense();
}

// Get recent expenses
function getExpenses($limit = 5) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM expenses WHERE user_id = ? ORDER BY date_added DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get recent income
function getIncomes($limit = 5) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM income WHERE user_id = ? ORDER BY date_added DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Apply no-cache headers to all pages
setNoCacheHeaders();
?>