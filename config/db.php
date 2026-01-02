<?php
/**
 * Database Configuration File
 * ProFix Masters - Service Marketplace Platform
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'profix_masters');

// Global database connection variable
$conn = null;

/**
 * Get database connection
 * @return mysqli|false Database connection object or false on failure
 */
function getDBConnection() {
    global $conn;
    
    // Return existing connection if available
    if ($conn && $conn->ping()) {
        return $conn;
    }
    
    // Create new connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database Connection Failed: " . $conn->connect_error);
        die("Database connection failed. Please try again later.");
    }
    
    // Set charset to utf8mb4 for full unicode support
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Close database connection
 */
function closeDBConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
        $conn = null;
    }
}

/**
 * Execute a prepared statement query
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types (e.g., "ssi" for string, string, int)
 * @param array $params Array of parameters
 * @return mysqli_stmt|false Statement object or false on failure
 */
function executeQuery($query, $types = "", $params = []) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Query Preparation Failed: " . $conn->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query Execution Failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

// Initialize connection
getDBConnection();
?>
