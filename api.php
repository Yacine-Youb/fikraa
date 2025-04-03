<?php
header("Content-Type: application/json");
require "config.php";

// الحصول على الطلب القادم
$method = $_SERVER['REQUEST_METHOD'];
$request = explode("/", trim($_SERVER['PATH_INFO'], "/"));

$table = $request[0] ?? null; // اسم الجدول
$id = $request[1] ?? null; // المعرف (إن وجد)

switch ($method) {
    case 'GET': // جلب البيانات
        if ($id) {
            $query = "SELECT * FROM $table WHERE id = $id";
        } else {
            $query = "SELECT * FROM $table";
        }
        $result = $conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    case 'POST': // إدخال بيانات جديدة
        $input = json_decode(file_get_contents("php://input"), true);
        $columns = implode(",", array_keys($input));
        $values = implode("','", array_values($input));
        $query = "INSERT INTO $table ($columns) VALUES ('$values')";
        $response = $conn->query($query);
        echo json_encode(["status" => $response ? "success" : "error"]);
        break;

    case 'PUT': // تعديل بيانات
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

    case 'DELETE': // حذف بيانات
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
?>
