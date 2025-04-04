<?php
require "config.php";

// Remove JSON header for HTML output
header("Content-Type: text/html; charset=UTF-8");

// Test family name
$familyName = "Baouchi";

// Get individuals with this family name
$query = "SELECT * FROM individuals WHERE last_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $familyName);
$stmt->execute();
$result = $stmt->get_result();

$individuals = [];
while ($row = $result->fetch_assoc()) {
    $individuals[] = $row;
}

echo "<h2>Individuals with last name '$familyName':</h2>";
echo "<pre>";
print_r($individuals);
echo "</pre>";

// Get all relationships for these individuals
$individualIds = array_column($individuals, 'id');
$placeholders = str_repeat('?,', count($individualIds) - 1) . '?';

$query = "SELECT * FROM family_relations 
          WHERE individual1_id IN ($placeholders) OR individual2_id IN ($placeholders)";
$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($individualIds) * 2), 
                 ...array_merge($individualIds, $individualIds));
$stmt->execute();
$result = $stmt->get_result();

$relations = [];
while ($row = $result->fetch_assoc()) {
    $relations[] = $row;
}

echo "<h2>Family Relations:</h2>";
echo "<pre>";
print_r($relations);
echo "</pre>";

// Get individual details for each relation
echo "<h2>Detailed Family Relations:</h2>";
foreach ($relations as $relation) {
    $id1 = $relation['individual1_id'];
    $id2 = $relation['individual2_id'];
    $type = $relation['relation_type'];
    
    $query = "SELECT * FROM individuals WHERE id IN (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id1, $id2);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $individuals = [];
    while ($row = $result->fetch_assoc()) {
        $individuals[$row['id']] = $row;
    }
    
    echo "<p>Relation: {$individuals[$id1]['first_name']} {$individuals[$id1]['last_name']} ({$individuals[$id1]['gender']}) - $type - {$individuals[$id2]['first_name']} {$individuals[$id2]['last_name']} ({$individuals[$id2]['gender']})</p>";
}

$conn->close();
?> 