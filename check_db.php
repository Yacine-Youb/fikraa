<?php
// Remove JSON header for HTML output
header("Content-Type: text/html; charset=UTF-8");

// Database connection parameters
$host = "localhost";
$user = "root";
$password = "";
$dbname = "fikraa";

echo "<h1>Database Connection Test</h1>";

// Create connection
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p>Connected successfully to MySQL server</p>";

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");
if ($result->num_rows > 0) {
    echo "<p>Database '$dbname' exists</p>";
    
    // Select the database
    $conn->select_db($dbname);
    
    // Show tables
    $result = $conn->query("SHOW TABLES");
    echo "<h2>Tables in database '$dbname':</h2>";
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Check if required tables exist
    $required_tables = ['individuals', 'family_relations'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        echo "<p style='color: red;'>Missing required tables: " . implode(", ", $missing_tables) . "</p>";
    } else {
        echo "<p style='color: green;'>All required tables exist</p>";
    }
    
    // Show sample data from individuals table
    echo "<h2>Sample data from individuals table:</h2>";
    $result = $conn->query("SELECT * FROM individuals LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        // Headers
        echo "<tr>";
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found in individuals table</p>";
    }
    
    // Show sample data from family_relations table
    echo "<h2>Sample data from family_relations table:</h2>";
    $result = $conn->query("SELECT * FROM family_relations LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        // Headers
        echo "<tr>";
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found in family_relations table</p>";
    }
} else {
    echo "<p style='color: red;'>Database '$dbname' does not exist</p>";
    
    // Show available databases
    echo "<h2>Available databases:</h2>";
    $result = $conn->query("SHOW DATABASES");
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
}

$conn->close();
?> 