<?php
// Set headers first
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Disable error display and enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

try {
    require "config.php";

    $method = $_SERVER['REQUEST_METHOD'];
    $table = $_GET['table'] ?? null;
    $id = $_GET['id'] ?? null;

    // Family tree endpoint
    if ($table === "family_tree") {
        getFamilyTree($conn);
        exit;
    }

    // Statistics endpoint
    if ($table === "statistics") {
        $stats = [];
        
        // Total individuals
        $query = "SELECT COUNT(*) as total FROM individuals";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting total individuals: " . $conn->error);
        }
        $stats['total_individuals'] = $result->fetch_assoc()['total'];
        
        // Total students
        $query = "SELECT COUNT(DISTINCT i.id) as total 
                  FROM individuals i 
                  LEFT JOIN social_status_info ssi ON i.id = ssi.individual_id 
                  WHERE ssi.occupation = 'student'";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting total students: " . $conn->error);
        }
        $stats['total_students'] = $result->fetch_assoc()['total'];
        
        // Total workers
        $query = "SELECT COUNT(DISTINCT i.id) as total 
                  FROM individuals i 
                  LEFT JOIN social_status_info ssi ON i.id = ssi.individual_id 
                  WHERE ssi.occupation = 'worker'";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting total workers: " . $conn->error);
        }
        $stats['total_workers'] = $result->fetch_assoc()['total'];
        
        // Total pupils
        $query = "SELECT COUNT(DISTINCT i.id) as total 
                  FROM individuals i 
                  LEFT JOIN social_status_info ssi ON i.id = ssi.individual_id 
                  WHERE ssi.occupation = 'pupil'";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting total pupils: " . $conn->error);
        }
        $stats['total_pupils'] = $result->fetch_assoc()['total'];
        
        // Total retired
        $query = "SELECT COUNT(DISTINCT i.id) as total 
                  FROM individuals i 
                  LEFT JOIN social_status_info ssi ON i.id = ssi.individual_id 
                  WHERE ssi.occupation = 'retired'";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting total retired: " . $conn->error);
        }
        $stats['total_retired'] = $result->fetch_assoc()['total'];

        // Family count
        $query = "SELECT COUNT(DISTINCT i.id) as total 
                  FROM families i";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting total retired: " . $conn->error);
        }
        $stats['total_families'] = $result->fetch_assoc()['total'];
        
        // Age groups
        $query = "SELECT 
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 18 THEN 1 ELSE 0 END) as age_0_18,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 19 AND 25 THEN 1 ELSE 0 END) as age_19_25,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 26 AND 40 THEN 1 ELSE 0 END) as age_26_40,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 41 AND 60 THEN 1 ELSE 0 END) as age_41_60,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) > 60 THEN 1 ELSE 0 END) as age_60_plus
                  FROM individuals";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting age groups: " . $conn->error);
        }
        $stats['age_groups'] = $result->fetch_assoc();
        
        // Gender statistics
        $query = "SELECT 
                    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as total_males,
                    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as total_females
                  FROM individuals";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting gender statistics: " . $conn->error);
        }
        $stats['gender_stats'] = $result->fetch_assoc();
        
        echo json_encode($stats);
        exit;
    } else if ($table === "filter_options") {
        $options = [];
        
        // Get unique marital statuses
        $query = "SELECT DISTINCT marital_status FROM individuals WHERE marital_status IS NOT NULL";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting marital statuses: " . $conn->error);
        }
        $options['marital_statuses'] = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['marital_status']) {
                $options['marital_statuses'][] = $row['marital_status'];
            }
        }
        
        // Get unique statuses
        $query = "SELECT DISTINCT status FROM individuals WHERE status IS NOT NULL";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting statuses: " . $conn->error);
        }
        $options['statuses'] = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['status']) {
                $options['statuses'][] = $row['status'];
            }
        }
        
        // Get unique residences
        $query = "SELECT DISTINCT residence FROM individuals WHERE residence IS NOT NULL";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Error getting residences: " . $conn->error);
        }
        $options['residences'] = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['residence']) {
                $options['residences'][] = $row['residence'];
            }
        }
        
        echo json_encode($options);
        exit;
    }

    // Check database tables endpoint
    if ($table === "check_tables") {
        try {
            $tables = ['individuals', 'family_relations', 'social_status_info', 'additional_social_status'];
            $results = [];
            
            foreach ($tables as $tableName) {
                // Check if table exists
                $checkQuery = "SHOW TABLES LIKE '$tableName'";
                $result = $conn->query($checkQuery);
                
                if ($result && $result->num_rows > 0) {
                    // Table exists, get structure
                    $describeQuery = "DESCRIBE $tableName";
                    $structureResult = $conn->query($describeQuery);
                    
                    if ($structureResult) {
                        $structure = [];
                        $columns = [];
                        while ($row = $structureResult->fetch_assoc()) {
                            $structure[] = $row;
                            $columns[] = $row['Field'];
                        }
                        $results[$tableName] = [
                            'exists' => true,
                            'structure' => $structure,
                            'columns' => $columns
                        ];
                        
                        // Check key information
                        $keyQuery = "SHOW KEYS FROM $tableName";
                        $keyResult = $conn->query($keyQuery);
                        if ($keyResult) {
                            $keys = [];
                            while ($keyRow = $keyResult->fetch_assoc()) {
                                $keys[] = $keyRow;
                            }
                            $results[$tableName]['keys'] = $keys;
                        }
                    } else {
                        $results[$tableName] = [
                            'exists' => true,
                            'error' => "Could not describe table: " . $conn->error
                        ];
                    }
                    
                    // Get row count
                    $countQuery = "SELECT COUNT(*) as count FROM $tableName";
                    $countResult = $conn->query($countQuery);
                    if ($countResult) {
                        $countRow = $countResult->fetch_assoc();
                        $results[$tableName]['row_count'] = $countRow['count'];
                    }
                } else {
                    $results[$tableName] = [
                        'exists' => false,
                        'error' => "Table does not exist"
                    ];
                }
            }
            
            // Check database version
            $versionQuery = "SELECT VERSION() as version";
            $versionResult = $conn->query($versionQuery);
            if ($versionResult) {
                $versionRow = $versionResult->fetch_assoc();
                $results['database_info'] = [
                    'version' => $versionRow['version'],
                    'server_info' => $conn->server_info,
                    'host_info' => $conn->host_info
                ];
            }
            
            echo json_encode([
                'status' => 'success',
                'tables' => $results
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    switch ($method) {
        case 'GET': 
            if ($table === "individuals") {
                // Log all GET parameters for debugging
                error_log("GET parameters: " . print_r($_GET, true));
                
                // Build filters based on GET parameters
                $filters = [];
                
                // Handle status filter (can be array or single value)
                if (isset($_GET['status'])) {
                    error_log("Status filter: " . print_r($_GET['status'], true));
                    
                    // Check if status is an array (status[])
                    if (is_array($_GET['status'])) {
                        $statusValues = array_map(function($val) use ($conn) {
                            return "'" . $conn->real_escape_string($val) . "'";
                        }, $_GET['status']);
                        $filters[] = "i.status IN (" . implode(',', $statusValues) . ")";
                    } else {
                        $filters[] = "i.status = '" . $conn->real_escape_string($_GET['status']) . "'";
                    }
                }
                
                // Handle last name filter
                if (isset($_GET['last_name']) && $_GET['last_name'] !== '') {
                    error_log("Last name filter: " . $_GET['last_name']);
                    $filters[] = "i.last_name LIKE '%" . $conn->real_escape_string($_GET['last_name']) . "%'";
                }
                
                // Handle special status filter (can be array or single value)
                if (isset($_GET['special_status'])) {
                    error_log("Special status filter: " . print_r($_GET['special_status'], true));
                    
                    // Check if special_status is an array
                    if (is_array($_GET['special_status'])) {
                        $specialStatusValues = array_map(function($val) use ($conn) {
                            return "'" . $conn->real_escape_string($val) . "'";
                        }, $_GET['special_status']);
                        $filters[] = "ass.status IN (" . implode(',', $specialStatusValues) . ")";
                    } else {
                        $filters[] = "ass.status = '" . $conn->real_escape_string($_GET['special_status']) . "'";
                    }
                }
                
                // Handle date range filters
                if (isset($_GET['from_date']) && $_GET['from_date'] !== '') {
                    error_log("From date filter: " . $_GET['from_date']);
                    $filters[] = "i.registration_date >= '" . $conn->real_escape_string($_GET['from_date']) . "'";
                }
                
                if (isset($_GET['to_date']) && $_GET['to_date'] !== '') {
                    error_log("To date filter: " . $_GET['to_date']);
                    $filters[] = "i.registration_date <= '" . $conn->real_escape_string($_GET['to_date']) . "'";
                }
                
                // Handle search filter
                if (isset($_GET['search']) && $_GET['search'] !== '') {
                    error_log("Search filter: " . $_GET['search']);
                    $search = $conn->real_escape_string($_GET['search']);
                    $filters[] = "(i.first_name LIKE '%$search%' OR i.last_name LIKE '%$search%' OR i.id LIKE '%$search%')";
                }
                
                // Handle occupation filter
                if (!empty($_GET['occupation'])) {
                    $filters[] = "ssi.occupation = '" . $conn->real_escape_string($_GET['occupation']) . "'";
                }
                
                // Handle marital status filter
                if (!empty($_GET['marital_status'])) {
                    $filters[] = "i.marital_status = '" . $conn->real_escape_string($_GET['marital_status']) . "'";
                }
                
                // Handle additional status filter
                if (!empty($_GET['additional_status'])) {
                    $filters[] = "ass.status = '" . $conn->real_escape_string($_GET['additional_status']) . "'";
                }

                // Build the WHERE clause
                $filterQuery = !empty($filters) ? "WHERE " . implode(" AND ", $filters) : "";
                
                // Execute the query with filters
                $query = "SELECT i.*, ssi.occupation, ssi.occupation_type, ssi.occupation_place, 
                                ssi.study_year, ssi.speciality, ssi.school, ssi.quran, ssi.retirement_info,
                                GROUP_CONCAT(ass.status) AS additional_statuses
                         FROM individuals i 
                         LEFT JOIN social_status_info ssi ON i.id = ssi.individual_id 
                         LEFT JOIN additional_social_status ass ON i.id = ass.individual_id 
                         $filterQuery 
                         GROUP BY i.id";
                
                // Log the query for debugging
                error_log("Filter Query: $query");
                
                $result = $conn->query($query);
                if (!$result) {
                    throw new Exception("Error fetching individuals: " . $conn->error);
                }
                
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                echo json_encode($data);
                exit;
            }
            $query = $id ? "SELECT * FROM $table WHERE id = $id" : "SELECT * FROM $table";
            $result = $conn->query($query);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        case 'POST':
            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid JSON data"
                ]);
                exit;
            }
            
            // Log the input data
            error_log("POST data for table $table: " . print_r($input, true));
            
            if ($table === "individuals") {
                // Log the full input data
                error_log("Full input data for new member: " . print_r($input, true));
                
                // Try simplified approach for debugging
                try {
                    // Start a transaction
                    $conn->begin_transaction();
                    
                    // Collect all main fields with validation
                    $first_name = $conn->real_escape_string($input['first_name'] ?? '');
                    $last_name = $conn->real_escape_string($input['last_name'] ?? '');
                    $gender = $conn->real_escape_string($input['gender'] ?? '');
                    $marital_status = $conn->real_escape_string($input['marital_status'] ?? '');
                    $birth_date = $conn->real_escape_string($input['birth_date'] ?? ''); 
                    $status = $conn->real_escape_string($input['status'] ?? 'active');
                    $phone = $conn->real_escape_string($input['phone'] ?? '');
                    $address = $conn->real_escape_string($input['address'] ?? '');
                    
                    // Log all fields
                    error_log("Processed fields: first_name=$first_name, last_name=$last_name, gender=$gender, " .
                             "marital_status=$marital_status, birth_date=$birth_date, status=$status, " .
                             "phone=$phone, address=$address");
                    
                    // Try method 1 - Prepared statement
                    try {
                        // Create a query including all fields
                        $query = "INSERT INTO individuals (first_name, last_name, gender, marital_status, birth_date, status, phone, address) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        error_log("Method 1 - Prepared statement query: " . $query);
                        
                        $stmt = $conn->prepare($query);
                        if (!$stmt) {
                            throw new Exception("Prepare error: " . $conn->error);
                        }
                        
                        // Bind parameters
                        $stmt->bind_param("ssssssss", $first_name, $last_name, $gender, $marital_status, 
                                         $birth_date, $status, $phone, $address);
                        
                        // Execute
                        if (!$stmt->execute()) {
                            throw new Exception("Execute error: " . $stmt->error);
                        }
                        
                        $individualId = $stmt->insert_id;
                        $method = "prepared statement";
                    } catch (Exception $e) {
                        error_log("Method 1 failed: " . $e->getMessage());
                        
                        // Try method 2 - Direct SQL query
                        $directQuery = "INSERT INTO individuals (first_name, last_name, gender, marital_status, birth_date, status, phone, address) 
                                    VALUES ('$first_name', '$last_name', '$gender', '$marital_status', '$birth_date', '$status', '$phone', '$address')";
                        error_log("Method 2 - Direct SQL query: " . $directQuery);
                        
                        $result = $conn->query($directQuery);
                        if (!$result) {
                            throw new Exception("Direct query error: " . $conn->error);
                        }
                        
                        $individualId = $conn->insert_id;
                        $method = "direct query";
                    }
                    
                    // Check if we actually inserted a row
                    if (!$individualId) {
                        throw new Exception("Insert appeared to succeed but no ID was returned");
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    
                    echo json_encode([
                        "status" => "success",
                        "message" => "Member added successfully (using $method)",
                        "id" => $individualId
                    ]);
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $conn->rollback();
                    
                    error_log("All methods failed: " . $e->getMessage());
                    
                    // Check database connection
                    if ($conn->ping()) {
                        error_log("Database connection is still good");
                    } else {
                        error_log("Database connection lost: " . $conn->error);
                    }
                    
                    // Get table status
                    $tableStatusQuery = "SHOW TABLE STATUS LIKE 'individuals'";
                    $tableStatusResult = $conn->query($tableStatusQuery);
                    if ($tableStatusResult) {
                        $tableStatus = $tableStatusResult->fetch_assoc();
                        error_log("Table status: " . print_r($tableStatus, true));
                    } else {
                        error_log("Unable to get table status: " . $conn->error);
                    }
                    
                    echo json_encode([
                        "status" => "error",
                        "message" => "Failed to add member",
                        "details" => $e->getMessage()
                    ]);
                }
                
                exit;
            } else {
                // For other tables, use the original code
                $columns = implode(",", array_keys($input));
                $values = implode("','", array_values($input));
                $query = "INSERT INTO $table ($columns) VALUES ('$values')";
                $response = $conn->query($query);
                echo json_encode(["status" => $response ? "success" : "error"]);
            }
            break;

        case 'PUT':
            if (!$id) {
                echo json_encode(["status" => "error", "message" => "Missing ID"]);
                exit;
            }
            $input = json_decode(file_get_contents("php://input"), true);
            $updates = [];
            foreach ($input as $key => $value) {
                $updates[] = "$key='$value'";
            }
            $query = "UPDATE $table SET " . implode(",", $updates) . " WHERE id = $id";
            $response = $conn->query($query);
            echo json_encode(["status" => $response ? "success" : "error"]);
            break;

        case 'DELETE':
            if (!$id) {
                echo json_encode(["status" => "error", "message" => "Missing ID"]);
                exit;
            }
            $query = "DELETE FROM $table WHERE id = $id";
            $response = $conn->query($query);
            echo json_encode(["status" => $response ? "success" : "error"]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid request"]);
    }

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "An error occurred",
        "details" => $e->getMessage()
    ]);
} finally {
    // Clean any output buffer
    ob_end_clean();
    $conn->close();
}

function getFamilyTree($conn) {
    $familyName = $_GET['family'] ?? '';
    
    if (empty($familyName)) {
        echo json_encode(["status" => "error", "message" => "Family name is required"]);
        return;
    }
    
    try {
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
        
        if (empty($individuals)) {
            echo json_encode(["status" => "error", "message" => "No family found with that name"]);
            return;
        }
        
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
        
        // Build the family tree structure
        $tree = buildFamilyTree($conn, $individuals, $relations);
        
        echo json_encode([
            "status" => "success",
            "data" => $tree
        ]);
        
    } catch(Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

/**
 * Builds a family tree structure based on the new relationship types
 */
function buildFamilyTree($conn, $individuals, $relations) {
    // Create a map of individuals by ID for quick lookup
    $individualsMap = [];
    foreach ($individuals as $individual) {
        $individualsMap[$individual['id']] = $individual;
    }
    
    // Create a map of relationships
    $relationsMap = [];
    foreach ($relations as $relation) {
        $id1 = $relation['individual1_id'];
        $id2 = $relation['individual2_id'];
        $relationship = $relation['relationship'];
        
        // Store both directions of the relationship
        if (!isset($relationsMap[$id1])) {
            $relationsMap[$id1] = [];
        }
        if (!isset($relationsMap[$id2])) {
            $relationsMap[$id2] = [];
        }
        
        // Store the relationship with the correct direction
        if ($relationship === 'Father') {
            // If id1 is the father, then id2 is the child
            $relationsMap[$id1][] = ['individual_id' => $id2, 'relationship' => 'Son/Daughter'];
            $relationsMap[$id2][] = ['individual_id' => $id1, 'relationship' => 'Father'];
        } else if ($relationship === 'Son' || $relationship === 'Daughter') {
            // If id1 is the child, then id2 is the parent
            $relationsMap[$id1][] = ['individual_id' => $id2, 'relationship' => 'Father'];
            $relationsMap[$id2][] = ['individual_id' => $id1, 'relationship' => $relationship];
        }
    }
    
    // Find the oldest generation (potential grandparents)
    $oldestGeneration = findOldestGeneration($individualsMap, $relationsMap);
    
    // Build the tree starting from the oldest generation
    $tree = [];
    foreach ($oldestGeneration as $rootId) {
        $tree[] = buildSubtree($rootId, $individualsMap, $relationsMap);
    }
    
    return [
        'individuals' => $individualsMap,
        'relations' => $relationsMap,
        'tree' => $tree
    ];
}

/**
 * Finds the oldest generation in the family tree
 */
function findOldestGeneration($individualsMap, $relationsMap) {
    $oldestGeneration = [];
    $visited = [];
    
    // Start with all individuals
    $allIds = array_keys($individualsMap);
    
    // For each individual, try to find their ancestors
    foreach ($allIds as $id) {
        if (isset($visited[$id])) continue;
        
        // Find the oldest ancestor for this individual
        $oldestAncestor = findOldestAncestor($id, $individualsMap, $relationsMap, $visited);
        
        if ($oldestAncestor && !in_array($oldestAncestor, $oldestGeneration)) {
            $oldestGeneration[] = $oldestAncestor;
        }
    }
    
    // If no oldest generation was found, use all individuals as roots
    if (empty($oldestGeneration)) {
        $oldestGeneration = $allIds;
    }
    
    return $oldestGeneration;
}

/**
 * Recursively finds the oldest ancestor for a given individual
 */
function findOldestAncestor($id, $individualsMap, $relationsMap, &$visited) {
    $visited[$id] = true;
    
    // If this individual has no relations, they are their own oldest ancestor
    if (!isset($relationsMap[$id]) || empty($relationsMap[$id])) {
        return $id;
    }
    
    // Look for parents in the relations
    $parents = [];
    foreach ($relationsMap[$id] as $relation) {
        if ($relation['relationship'] === 'Father') {
            $parents[] = $relation['individual_id'];
        }
    }
    
    // If no parents found, this individual is the oldest ancestor
    if (empty($parents)) {
        return $id;
    }
    
    // Recursively find the oldest ancestor for each parent
    $oldestAncestors = [];
    foreach ($parents as $parentId) {
        if (!isset($visited[$parentId])) {
            $oldestAncestors[] = findOldestAncestor($parentId, $individualsMap, $relationsMap, $visited);
        }
    }
    
    // If no oldest ancestors found among parents, use the individual itself
    if (empty($oldestAncestors)) {
        return $id;
    }
    
    // Return the oldest ancestor (assuming the first one is the oldest)
    return $oldestAncestors[0];
}

/**
 * Builds a subtree starting from a root individual
 */
function buildSubtree($rootId, $individualsMap, $relationsMap) {
    $root = $individualsMap[$rootId];
    $subtree = [
        'id' => $rootId,
        'name' => $root['first_name'] . ' ' . $root['last_name'],
        'gender' => $root['gender'],
        'birth_date' => $root['birth_date'],
        'children' => []
    ];
    
    // Find children
    if (isset($relationsMap[$rootId])) {
        foreach ($relationsMap[$rootId] as $relation) {
            if ($relation['relationship'] === 'Son' || $relation['relationship'] === 'Daughter') {
                $childId = $relation['individual_id'];
                // Check if this child is already in the tree to avoid duplicates
                $childExists = false;
                foreach ($subtree['children'] as $existingChild) {
                    if ($existingChild['id'] === $childId) {
                        $childExists = true;
                        break;
                    }
                }
                if (!$childExists) {
                    $subtree['children'][] = buildSubtree($childId, $individualsMap, $relationsMap);
                }
            }
        }
    }
    
    return $subtree;
}
?>
