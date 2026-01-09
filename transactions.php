<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// ตรวจสอบการ Login
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$user_info = get_user_info($user_id);

// Filter parameters
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('n');
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Build SQL query
$sql = "SELECT 
    t.*,
    c.category_name,
    c.icon,
    c.color
FROM transactions t
LEFT JOIN categories c ON t.category_id = c.category_id
WHERE t.user_id = $user_id";

if ($filter_type !== 'all') {
    $sql .= " AND t.transaction_type = '$filter_type'";
}

$sql .= " AND MONTH(t.transaction_date) = $filter_month 
          AND YEAR(t.transaction_date) = $filter_year";

$sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";

$result = $conn->query($sql);
$transactions = [];
$total_income = 0;
$total_expense = 0;

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
    if ($row['transaction_type'] === 'income') {
        $total_income += $row['amount'];
    } else {
        $total_expense += $row['amount'];
    }
}

// แปลงเดือนเป็นภาษาไทย
$thai_months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการทั้งหมด - THIRDIOS</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo" id="logoToggle">
                <img src="images/logo.png" alt="Third Logo">
                <span class="logo-text">THIRDIOS</span>
                <i class="fas fa-chevron-left toggle-arrow" id="toggleArrow"></i>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-th-large"></i>
                        <span class="nav-text">หน้าหลัก</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_income.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span class="nav-text">เพิ่มรายรับ</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_expense.php" class="nav-link">
                        <i class="fas fa-minus-circle"></i>
                        <span class="nav-text">เพิ่มรายจ่าย</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="transactions.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        <span class="nav-text">รายการทั้งหมด</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user-circle"></i>
                        <span class="nav-text">จัดการบัญชี</span>
                    </a>
                </li>
                <?php if ($user_info['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="admin/index.php" class="nav-link" style="background: rgba(255, 215, 0, 0.2);">
                        <i class="fas fa-user-shield"></i>
                        <span class="nav-text">ระบบ Admin</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="#" onclick="confirmLogout()" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-text">ออกจากระบบ</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <i class="fas fa-user-circle"></i>
                <span class="user-name"><?php echo htmlspecialchars($user_info['full_name']); ?></span>
            </div>
        </div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="menu-btn" id="menuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>รายการทั้งหมด - THIRDIOS</h1>
            </div>
            <div class="header-right">
                <button class="theme-toggle" id="themeToggle" title="สลับธีม">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </header>

        <!-- Content -->
        <div class="dashboard-container">
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label>ประเภท</label>
                        <select name="type" onchange="this.form.submit()">
                            <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                            <option value="income" <?php echo $filter_type === 'income' ? 'selected' : ''; ?>>รายรับ</option>
                            <option value="expense" <?php echo $filter_type === 'expense' ? 'selected' : ''; ?>>รายจ่าย</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>เดือน</label>
                        <select name="month" onchange="this.form.submit()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $filter_month == $m ? 'selected' : ''; ?>>
                                    <?php echo $thai_months[$m]; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>ปี</label>
                        <select name="year" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>>
                                    <?php echo $y + 543; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
                
                <div class="filter-summary">
                    <div class="summary-item income">
                        <i class="fas fa-arrow-up"></i>
                        <span>รายรับ: ฿<?php echo number_format($total_income, 2); ?></span>
                    </div>
                    <div class="summary-item expense">
                        <i class="fas fa-arrow-down"></i>
                        <span>รายจ่าย: ฿<?php echo number_format($total_expense, 2); ?></span>
                    </div>
                    <div class="summary-item balance">
                        <i class="fas fa-wallet"></i>
                        <span>คงเหลือ: ฿<?php echo number_format($total_income - $total_expense, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="transactions-full-list">
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $trans): ?>
                        <div class="transaction-item-full <?php echo $trans['transaction_type']; ?>">
                            <div class="trans-icon" style="background: <?php echo $trans['color']; ?>20; color: <?php echo $trans['color']; ?>">
                                <i class="fas <?php echo $trans['icon']; ?>"></i>
                            </div>
                            <div class="trans-details-full">
                                <h4><?php echo htmlspecialchars($trans['category_name']); ?></h4>
                                <p><?php echo htmlspecialchars($trans['description']); ?></p>
                                <small><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?></small>
                            </div>
                            <div class="trans-amount-full <?php echo $trans['transaction_type']; ?>">
                                <?php echo $trans['transaction_type'] === 'income' ? '+' : '-'; ?>
                                ฿<?php echo number_format($trans['amount'], 2); ?>
                            </div>
                            <div class="trans-actions">
                                <button class="btn-delete" onclick="deleteTransaction(<?php echo $trans['transaction_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>ไม่พบรายการ</p>
                        <small>ลองเปลี่ยนเดือนหรือปี หรือเพิ่มรายการใหม่</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="script/dashboard.js"></script>
</body>
</html>
