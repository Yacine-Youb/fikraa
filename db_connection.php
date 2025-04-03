<?php
function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = ""; // Default XAMPP password is empty
    $dbname = "fikraa"; // Your database name

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

$conn = getDbConnection();
?> 