<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// ฟังก์ชันตรวจสอบการ Login
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ฟังก์ชันตรวจสอบสิทธิ์ Admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// ฟังก์ชัน Login
function login_user($username, $password) {
    global $conn;
    
    $username = escape_string($username);
    $sql = "SELECT * FROM users WHERE username = '$username' OR email = '$username' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (verify_password($password, $user['password'])) {
            if ($user['status'] === 'active') {
                // สร้าง Session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                // อัพเดท last_login
                $user_id = $user['user_id'];
                $conn->query("UPDATE users SET last_login = NOW() WHERE user_id = $user_id");
                
                // บันทึก Activity Log
                log_activity($user_id, 'เข้าสู่ระบบ', 'เข้าสู่ระบบสำเร็จ');
                
                return ['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ', 'role' => $user['role']];
            } else {
                return ['success' => false, 'message' => 'บัญชีของคุณถูกระงับ กรุณาติดต่อผู้ดูแลระบบ'];
            }
        } else {
            return ['success' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'];
        }
    } else {
        return ['success' => false, 'message' => 'ไม่พบชื่อผู้ใช้หรืออีเมลนี้'];
    }
}

// ฟังก์ชัน Register
function register_user($username, $email, $password, $full_name) {
    global $conn;
    
    // ตรวจสอบข้อมูลซ้ำ
    $username = escape_string($username);
    $email = escape_string($email);
    
    $check_sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        if ($existing['username'] === $username) {
            return ['success' => false, 'message' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว'];
        } else {
            return ['success' => false, 'message' => 'อีเมลนี้ถูกใช้งานแล้ว'];
        }
    }
    
    // สร้างบัญชีใหม่
    $full_name = escape_string($full_name);
    $hashed_password = hash_password($password);
    
    $sql = "INSERT INTO users (username, email, password, full_name, role) 
            VALUES ('$username', '$email', '$hashed_password', '$full_name', 'user')";
    
    if ($conn->query($sql)) {
        $user_id = $conn->insert_id;
        log_activity($user_id, 'สมัครสมาชิก', 'สมัครสมาชิกใหม่');
        return ['success' => true, 'message' => 'สมัครสมาชิกสำเร็จ', 'user_id' => $user_id];
    } else {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error];
    }
}

// ฟังก์ชัน Logout
function logout_user() {
    if (isset($_SESSION['user_id'])) {
        log_activity($_SESSION['user_id'], 'ออกจากระบบ', 'ออกจากระบบ');
    }
    session_destroy();
    return ['success' => true, 'message' => 'ออกจากระบบสำเร็จ'];
}

// ฟังก์ชันบันทึก Activity Log
function log_activity($user_id, $action, $details = '') {
    global $conn;
    $user_id = (int)$user_id;
    $action = escape_string($action);
    $details = escape_string($details);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
            VALUES ($user_id, '$action', '$details', '$ip_address')";
    $conn->query($sql);
}

// ฟังก์ชันเปลี่ยนรหัสผ่าน
function change_password($user_id, $old_password, $new_password) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT password FROM users WHERE user_id = $user_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (verify_password($old_password, $user['password'])) {
            $new_hash = hash_password($new_password);
            $update_sql = "UPDATE users SET password = '$new_hash' WHERE user_id = $user_id";
            
            if ($conn->query($update_sql)) {
                log_activity($user_id, 'เปลี่ยนรหัสผ่าน', 'เปลี่ยนรหัสผ่านสำเร็จ');
                return ['success' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ'];
            }
        } else {
            return ['success' => false, 'message' => 'รหัสผ่านเดิมไม่ถูกต้อง'];
        }
    }
    
    return ['success' => false, 'message' => 'เกิดข้อผิดพลาด'];
}

// ฟังก์ชันดึงข้อมูลผู้ใช้
function get_user_info($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT user_id, username, email, full_name, role, profile_image, created_at, last_login 
            FROM users WHERE user_id = $user_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// ฟังก์ชันตรวจสอบและบังคับให้ login
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
?>
