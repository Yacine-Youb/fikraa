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
    }

    switch ($method) {
        case 'GET': 
            $filters = [];
            if (!empty($_GET['occupation'])) {
                $filters[] = "ssi.occupation = '" . $conn->real_escape_string($_GET['occupation']) . "'";
            }
            if (!empty($_GET['marital_status'])) {
                $filters[] = "i.marital_status = '" . $conn->real_escape_string($_GET['marital_status']) . "'";
            }
            if (!empty($_GET['additional_status'])) {
                $filters[] = "ass.status = '" . $conn->real_escape_string($_GET['additional_status']) . "'";
            }

            $filterQuery = !empty($filters) ? "WHERE " . implode(" AND ", $filters) : "";

            if ($table === "individuals") {
                $query = "SELECT i.*, ssi.occupation, ssi.occupation_type, ssi.occupation_place, 
                                ssi.study_year, ssi.speciality, ssi.school, ssi.quran, ssi.retirement_info,
                                GROUP_CONCAT(ass.status) AS additional_statuses
                         FROM individuals i 
                         LEFT JOIN social_status_info ssi ON i.id = ssi.individual_id 
                         LEFT JOIN additional_social_status ass ON i.id = ass.individual_id 
                         $filterQuery 
                         GROUP BY i.id";
            } else {
                $query = $id ? "SELECT * FROM $table WHERE id = $id" : "SELECT * FROM $table";
            }

            $result = $conn->query($query);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        case 'POST':
            $input = json_decode(file_get_contents("php://input"), true);
            $columns = implode(",", array_keys($input));
            $values = implode("','", array_values($input));
            $query = "INSERT INTO $table ($columns) VALUES ('$values')";
            $response = $conn->query($query);
            echo json_encode(["status" => $response ? "success" : "error"]);
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
?>
