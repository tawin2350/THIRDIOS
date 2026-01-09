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

// ดึงสรุปยอดเดือนปัจจุบัน
$current_month = date('n');
$current_year = date('Y');

$sql_summary = "SELECT 
    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense,
    COUNT(*) as total_transactions
FROM transactions 
WHERE user_id = $user_id 
AND MONTH(transaction_date) = $current_month 
AND YEAR(transaction_date) = $current_year";

$result = $conn->query($sql_summary);
$summary = $result->fetch_assoc();
$balance = $summary['total_income'] - $summary['total_expense'];

// ดึงข้อมูลกราฟ 5 เดือนล่าสุด
$sql_chart = "SELECT 
    YEAR(transaction_date) as year,
    MONTH(transaction_date) as month,
    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
    SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
FROM transactions 
WHERE user_id = $user_id 
AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 4 MONTH)
GROUP BY YEAR(transaction_date), MONTH(transaction_date)
ORDER BY year, month";

$chart_result = $conn->query($sql_chart);
$chart_data = [];
while ($row = $chart_result->fetch_assoc()) {
    $chart_data[] = $row;
}

// ดึงรายการล่าสุด
$sql_recent = "SELECT 
    t.*,
    c.category_name,
    c.icon,
    c.color
FROM transactions t
LEFT JOIN categories c ON t.category_id = c.category_id
WHERE t.user_id = $user_id
ORDER BY t.transaction_date DESC, t.created_at DESC
LIMIT 6";

$recent_result = $conn->query($sql_recent);
$recent_transactions = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_transactions[] = $row;
}

// ดึงหมวดหมู่
$sql_categories = "SELECT * FROM categories ORDER BY category_type, category_name";
$categories_result = $conn->query($sql_categories);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
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
    <title>Dashboard - THIRDIOS</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo" id="logoToggle">
                <img src="images/logo.PNG" alt="Third Logo">
                <span class="logo-text">THIRDIOS</span>
                <i class="fas fa-chevron-left toggle-arrow" id="toggleArrow"></i>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
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
                <li class="nav-item">
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
                <h1>ระบบวิเคราะห์รายรับรายจ่าย - THIRDIOS</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="ค้นหา" id="searchInput">
                </div>
                <button class="theme-toggle" id="themeToggle" title="สลับธีม">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="badge">0</span>
                </button>
                <div class="header-actions">
                    <a href="add_income.php" class="btn-primary">
                        <i class="fas fa-plus"></i> รายรับ
                    </a>
                    <a href="add_expense.php" class="btn-secondary">
                        <i class="fas fa-minus"></i> รายจ่าย
                    </a>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card income-card">
                    <div class="card-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="card-content">
                        <h3>รายรับเดือนนี้</h3>
                        <p class="amount">฿<?php echo number_format($summary['total_income'], 2); ?></p>
                        <small><?php echo $thai_months[$current_month]; ?> <?php echo $current_year + 543; ?></small>
                    </div>
                </div>

                <div class="summary-card expense-card">
                    <div class="card-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="card-content">
                        <h3>รายจ่ายเดือนนี้</h3>
                        <p class="amount">฿<?php echo number_format($summary['total_expense'], 2); ?></p>
                        <small><?php echo $thai_months[$current_month]; ?> <?php echo $current_year + 543; ?></small>
                    </div>
                </div>

                <div class="summary-card balance-card <?php echo $balance >= 0 ? 'positive' : 'negative'; ?>">
                    <div class="card-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="card-content">
                        <h3>ยอดคงเหลือ</h3>
                        <p class="amount">฿<?php echo number_format($balance, 2); ?></p>
                        <small><?php echo $balance >= 0 ? 'เกินมา' : 'ขาดไป'; ?> <?php echo abs($balance) > 0 ? '฿'.number_format(abs($balance), 2) : ''; ?></small>
                    </div>
                </div>
            </div>

            <!-- Left Section -->
            <div class="left-section">
                <!-- Recent Transactions -->
                <div class="transactions-card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> รายการล่าสุด</h3>
                        <a href="transactions.php" class="btn-view-all">ดูทั้งหมด</a>
                    </div>
                    <div class="transactions-list">
                        <?php if (count($recent_transactions) > 0): ?>
                            <?php foreach ($recent_transactions as $trans): ?>
                                <div class="transaction-item <?php echo $trans['transaction_type']; ?>">
                                    <div class="trans-icon" style="background: <?php echo $trans['color']; ?>20; color: <?php echo $trans['color']; ?>">
                                        <i class="fas <?php echo $trans['icon']; ?>"></i>
                                    </div>
                                    <div class="trans-details">
                                        <h4><?php echo htmlspecialchars($trans['category_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($trans['description']); ?></p>
                                        <small><?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?></small>
                                    </div>
                                    <div class="trans-amount <?php echo $trans['transaction_type']; ?>">
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
                                <p>ยังไม่มีรายการ</p>
                                <small>เริ่มบันทึกรายรับรายจ่ายของคุณ</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="right-section">
                <!-- Chart Card -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-line"></i> กราฟรายรับรายจ่าย (4 เดือนล่าสุด)</h3>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <?php foreach ($chart_data as $data): ?>
                            <span><?php echo $thai_months[$data['month']]; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // ข้อมูล Categories สำหรับ JavaScript
        const categories = <?php echo json_encode($categories); ?>;
        const chartData = <?php echo json_encode($chart_data); ?>;
        const thaiMonths = <?php echo json_encode($thai_months); ?>;
    </script>
    <script src="script/dashboard.js"></script>
</body>
</html>
