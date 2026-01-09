<?php
// การตั้งค่าเชื่อมต่อฐานข้อมูล (Railway MySQL)
define('DB_HOST', 'ballast.proxy.rlwy.net');
define('DB_USER', 'root');
define('DB_PASS', 'ELvjVbaLLkpEcnYbGbwkkPekEODGlKds');
define('DB_NAME', 'railway');
define('DB_PORT', 32938);

// สร้างการเชื่อมต่อ
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตั้งค่า charset เป็น utf8mb4
$conn->set_charset("utf8mb4");

// ฟังก์ชันป้องกัน SQL Injection
function escape_string($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

// ฟังก์ชัน Hash Password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// ฟังก์ชันตรวจสอบ Password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}
?>
