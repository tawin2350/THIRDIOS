<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                exit;
            }
            
            $result = login_user($username, $password);
            echo json_encode($result);
        }
        break;
        
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $full_name = trim($_POST['full_name']);
            
            // Validation
            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                exit;
            }
            
            if (strlen($username) < 4 || strlen($username) > 20) {
                echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้ต้องมีความยาว 4-20 ตัวอักษร']);
                exit;
            }
            
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้ต้องเป็นอักษรภาษาอังกฤษ ตัวเลข หรือ _ เท่านั้น']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'รูปแบบอีเมลไม่ถูกต้อง']);
                exit;
            }
            
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร']);
                exit;
            }
            
            $result = register_user($username, $email, $password, $full_name);
            echo json_encode($result);
        }
        break;
        
    case 'logout':
        $result = logout_user();
        echo json_encode($result);
        break;
        
    case 'change_password':
        if (!is_logged_in()) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            
            if (empty($old_password) || empty($new_password)) {
                echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                exit;
            }
            
            if (strlen($new_password) < 6) {
                echo json_encode(['success' => false, 'message' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร']);
                exit;
            }
            
            $result = change_password($_SESSION['user_id'], $old_password, $new_password);
            echo json_encode($result);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
