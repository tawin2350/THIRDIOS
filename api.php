<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// ตรวจสfอบการ login
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อนใช้งาน']);
    exit;
}

header('Content-Type: application/json');

// ฟังก์ชันดึงสรุปยอดรายรับรายจ่าย
function get_summary($user_id, $month = null, $year = null) {
    global $conn;
    $user_id = (int)$user_id;
    
    $where = "user_id = $user_id";
    if ($month && $year) {
        $where .= " AND MONTH(transaction_date) = $month AND YEAR(transaction_date) = $year";
    } elseif ($year) {
        $where .= " AND YEAR(transaction_date) = $year";
    }
    
    $sql = "SELECT 
                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense,
                COUNT(*) as total_transactions
            FROM transactions 
            WHERE $where";
    
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    
    $data['balance'] = $data['total_income'] - $data['total_expense'];
    
    return $data;
}

// ฟังก์ชันดึงข้อมูลกราฟรายเดือน
function get_monthly_chart($user_id, $year) {
    global $conn;
    $user_id = (int)$user_id;
    $year = (int)$year;
    
    $sql = "SELECT 
                MONTH(transaction_date) as month,
                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
            FROM transactions 
            WHERE user_id = $user_id AND YEAR(transaction_date) = $year
            GROUP BY MONTH(transaction_date)
            ORDER BY month";
    
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// ฟังก์ชันดึงรายการทรานแซคชั่น
function get_transactions($user_id, $limit = 10, $offset = 0, $type = null, $month = null, $year = null) {
    global $conn;
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $where = "t.user_id = $user_id";
    
    if ($type && in_array($type, ['income', 'expense'])) {
        $where .= " AND t.transaction_type = '$type'";
    }
    
    if ($month && $year) {
        $where .= " AND MONTH(t.transaction_date) = $month AND YEAR(t.transaction_date) = $year";
    }
    
    $sql = "SELECT 
                t.*,
                c.category_name,
                c.icon,
                c.color
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.category_id
            WHERE $where
            ORDER BY t.transaction_date DESC, t.created_at DESC
            LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// ฟังก์ชันเพิ่มรายการ
function add_transaction($user_id, $category_id, $type, $amount, $description, $date) {
    global $conn;
    
    $user_id = (int)$user_id;
    $category_id = (int)$category_id;
    $type = escape_string($type);
    $amount = (float)$amount;
    $description = escape_string($description);
    $date = escape_string($date);
    
    $sql = "INSERT INTO transactions (user_id, category_id, transaction_type, amount, description, transaction_date)
            VALUES ($user_id, $category_id, '$type', $amount, '$description', '$date')";
    
    if ($conn->query($sql)) {
        log_activity($user_id, 'เพิ่มรายการ', "เพิ่มรายการ $type จำนวน $amount บาท");
        return ['success' => true, 'message' => 'เพิ่มรายการสำเร็จ', 'id' => $conn->insert_id];
    }
    
    return ['success' => false, 'message' => 'เกิดข้อผิดพลาด'];
}

// ฟังก์ชันลบรายการ
function delete_transaction($transaction_id, $user_id) {
    global $conn;
    
    $transaction_id = (int)$transaction_id;
    $user_id = (int)$user_id;
    
    $sql = "DELETE FROM transactions WHERE transaction_id = $transaction_id AND user_id = $user_id";
    
    if ($conn->query($sql)) {
        log_activity($user_id, 'ลบรายการ', "ลบรายการ ID: $transaction_id");
        return ['success' => true, 'message' => 'ลบรายการสำเร็จ'];
    }
    
    return ['success' => false, 'message' => 'เกิดข้อผิดพลาด'];
}

// ฟังก์ชันดึงหมวดหมู่
function get_categories($type = null) {
    global $conn;
    
    $where = "1=1";
    if ($type && in_array($type, ['income', 'expense'])) {
        $where = "category_type = '$type'";
    }
    
    $sql = "SELECT * FROM categories WHERE $where ORDER BY category_name";
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// จัดการ API Requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

switch ($action) {
    case 'get_summary':
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        
        $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        
        $data = get_summary($user_id, $month, $year);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'get_chart':
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $data = get_monthly_chart($user_id, $year);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'get_transactions':
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        
        $data = get_transactions($user_id, $limit, $offset, $type, $month, $year);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'add_transaction':
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category_id = $_POST['category_id'];
            $type = $_POST['type'];
            $amount = $_POST['amount'];
            $description = $_POST['description'];
            $date = $_POST['date'];
            
            $result = add_transaction($user_id, $category_id, $type, $amount, $description, $date);
            echo json_encode($result);
        }
        break;
        
    case 'delete_transaction':
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        
        $transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $result = delete_transaction($transaction_id, $user_id);
        echo json_encode($result);
        break;
        
    case 'get_categories':
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $data = get_categories($type);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
