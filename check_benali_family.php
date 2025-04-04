<?php
// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Include database connection
require_once 'config.php';

// Check if connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Benali Family Data Check</h1>";

// 1. Check all individuals with last name "Benali"
$query = "SELECT * FROM individuals WHERE last_name = 'Benali'";
$result = $conn->query($query);

echo "<h2>Individuals with last name 'Benali'</h2>";
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Gender</th><th>Birth Date</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . "</td>";
        echo "<td>" . $row['last_name'] . "</td>";
        echo "<td>" . $row['gender'] . "</td>";
        echo "<td>" . $row['birth_date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No individuals found with last name 'Benali'</p>";
}

// 2. Check all family relations involving Benali individuals
$query = "SELECT fr.*, 
          i1.first_name as individual1_first_name, i1.last_name as individual1_last_name, i1.gender as individual1_gender,
          i2.first_name as individual2_first_name, i2.last_name as individual2_last_name, i2.gender as individual2_gender
          FROM family_relations fr
          JOIN individuals i1 ON fr.individual1_id = i1.id
          JOIN individuals i2 ON fr.individual2_id = i2.id
          WHERE i1.last_name = 'Benali' OR i2.last_name = 'Benali'";
$result = $conn->query($query);

echo "<h2>Family Relations for Benali Family</h2>";
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Individual 1</th><th>Individual 2</th><th>Relationship</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['individual1_first_name'] . " " . $row['individual1_last_name'] . " (" . $row['individual1_gender'] . ")</td>";
        echo "<td>" . $row['individual2_first_name'] . " " . $row['individual2_last_name'] . " (" . $row['individual2_gender'] . ")</td>";
        echo "<td>" . $row['relationship'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No family relations found for Benali family</p>";
}

// 3. Check the getFamilyTree function output for Benali
echo "<h2>getFamilyTree Function Output for Benali</h2>";
echo "<pre>";

// Include the api.php file to access the getFamilyTree function
require_once 'api.php';

// Call the getFamilyTree function directly
$_GET['family'] = 'Benali';
$output = getFamilyTree($conn);

// Display the output
echo json_encode($output, JSON_PRETTY_PRINT);
echo "</pre>";

// 4. Check the processTreeDataForD3 function output
echo "<h2>processTreeDataForD3 Function Output</h2>";
echo "<pre>";

// Include the index5.html file to access the processTreeDataForD3 function
// This is a bit tricky since it's JavaScript, so we'll simulate it in PHP
if (isset($output['data']['tree'])) {
    $treeData = $output['data']['tree'];
    
    // Simulate the processTreeDataForD3 function
    $processedData = processTreeDataForD3($treeData);
    
    // Display the processed data
    echo json_encode($processedData, JSON_PRETTY_PRINT);
} else {
    echo "No tree data available";
}
echo "</pre>";

// Helper function to simulate processTreeDataForD3
function processTreeDataForD3($treeData) {
    // If we have multiple root families, create a virtual root
    if (count($treeData) > 1) {
        return [
            'name' => 'Family Roots',
            'gender' => 'Male',
            'children' => array_map('processFamilyNode', $treeData)
        ];
    } else if (count($treeData) === 1) {
        return processFamilyNode($treeData[0]);
    } else {
        return ['name' => 'No Data', 'gender' => 'Male'];
    }
}

// Helper function to simulate processFamilyNode
function processFamilyNode($node) {
    $processedNode = [
        'name' => $node['name'],
        'gender' => $node['gender'],
        'birth_date' => $node['birth_date'] ?? null
    ];
    
    // Add children if any
    if (isset($node['children']) && count($node['children']) > 0) {
        $processedNode['children'] = array_map('processFamilyNode', $node['children']);
    }
    
    return $processedNode;
}

// Close the database connection
$conn->close();
?> 