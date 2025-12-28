<?php
// database.php - FIXED VERSION
// Location: /mnt/c/Users/yahya/OneDrive/Desktop/auto/config/database.php

// Use EXACTLY these credentials (same as your test)
$host = '172.21.192.1';       // 
$user = 'wsl_user';
$pass = 'wsl_password';
$dbname = 'car_rental';

// Force TCP connection (not socket)
$port = 3306;

// Create connection with explicit error handling
$conn = mysqli_init();

// Set connection timeout
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

// Connect using TCP/IP
if (!$conn->real_connect($host, $user, $pass, $dbname, $port)) {
    // Detailed error
    $error = "Connection failed: " . mysqli_connect_error();
    $error .= "\n\nDebug Info:";
    $error .= "\n- Host: " . $host;
    $error .= "\n- User: " . $user;
    $error .= "\n- Database: " . $dbname;
    $error .= "\n- Port: " . $port;
    $error .= "\n\nTry from WSL2: mysql -u $user -p$pass -h $host -e 'SELECT 1'";
    
    die("<h3>Database Connection Error</h3><pre>" . htmlspecialchars($error) . "</pre>");
}

// Set charset
$conn->set_charset("utf8mb4");

// Optional: Verify connection worked
// $result = $conn->query("SELECT 1 as test");
// if (!$result) {
//     die("Connection test failed: " . $conn->error);
// }

// Connection successful
// echo "✓ Database connected!<br>";

// Make it available globally
$GLOBALS['conn'] = $conn;
?>