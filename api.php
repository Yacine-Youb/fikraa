<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$request = explode("/", trim($_SERVER['PATH_INFO'], "/"));

$table = $request[0] ?? null;
$id = $request[1] ?? null;

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

$conn->close();
