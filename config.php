<?php
// Set headers first
header("Content-Type: application/json; charset=UTF-8");

// Disable error display and enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$host = "localhost"; // اسم المضيف
$user = "root"; // اسم المستخدم
$password = ""; // كلمة المرور (افتراضيًا فارغ في XAMPP)
$dbname = "associationsdb"; // اسم قاعدة البيانات

// إنشاء اتصال
$conn = new mysqli($host, $user, $password, $dbname);

// فحص الاتصال
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed",
        "details" => $conn->connect_error
    ]));
}

// تعيين الترميز إلى UTF-8
if (!$conn->set_charset("utf8")) {
    error_log("Error setting charset: " . $conn->error);
    die(json_encode([
        "status" => "error",
        "message" => "Error setting charset",
        "details" => $conn->error
    ]));
}
?>
