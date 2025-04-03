<?php
$host = "localhost"; // اسم المضيف
$user = "root"; // اسم المستخدم
$password = ""; // كلمة المرور (افتراضيًا فارغ في XAMPP)
$dbname = "Associationsdb"; // اسم قاعدة البيانات

// إنشاء اتصال
$conn = new mysqli($host, $user, $password, $dbname);

// فحص الاتصال
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// تعيين الترميز إلى UTF-8
$conn->set_charset("utf8");
?>
