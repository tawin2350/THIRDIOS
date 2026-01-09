<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// ตรวจสอบการ login และต้องเป็น admin
require_login();

$user_id = $_SESSION['user_id'];
$user_info = get_user_info($user_id);

if ($user_info['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// ดึงข้อมูลรายการทั้งหมด
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? 'all';
$user_filter = $_GET['user'] ?? 'all';

$sql = "SELECT t.*, u.username, c.category_name 
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.user_id
        LEFT JOIN categories c ON t.category_id = c.category_id
        WHERE 1=1";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (t.description LIKE '%$search%' OR u.username LIKE '%$search%' OR c.category_name LIKE '%$search%')";
}

if ($type_filter !== 'all') {
    $type_filter = $conn->real_escape_string($type_filter);
    $sql .= " AND t.transaction_type = '$type_filter'";
}

if ($user_filter !== 'all') {
    $user_filter = (int)$user_filter;
    $sql .= " AND t.user_id = $user_filter";
}

$sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC LIMIT 100";
$transactions = $conn->query($sql);

// ดึงรายชื่อผู้ใช้สำหรับ filter
$users_sql = "SELECT user_id, username FROM users ORDER BY username";
$users = $conn->query($users_sql);

// คำนวณสรุป
$summary_sql = "SELECT 
    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense
    FROM transactions";
$summary = $conn->query($summary_sql)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการทั้งหมด - THIRDIOS Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-bar {
            background: var(--bg-white);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-sm);
        }
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        .filter-item label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        .filter-item input,
        .filter-item select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            background: var(--bg-light);
            color: var(--text-dark);
        }
        .btn-filter {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 26px;
        }
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .summary-bar {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .summary-box {
            flex: 1;
            padding: 15px 20px;
            border-radius: 12px;
            font-weight: 600;
        }
        .summary-box.income {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }
        .summary-box.expense {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }
        .summary-box.balance {
            background: rgba(157, 124, 216, 0.1);
            color: var(--primary-purple);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo" id="logoToggle">
                <img src="../images/logo.PNG" alt="Third Logo">
                <span class="logo-text">THIRDIOS ADMIN</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span class="nav-text">ภาพรวม</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">จัดการผู้ใช้</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="transactions.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        <span class="nav-text">รายการทั้งหมด</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="categories.php" class="nav-link">
                        <i class="fas fa-tags"></i>
                        <span class="nav-text">จัดการหมวดหมู่</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">หน้าผู้ใช้</span>
                    </a>
                </li>
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
                <i class="fas fa-user-shield"></i>
                <span class="user-name"><?php echo htmlspecialchars($user_info['full_name']); ?></span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="header">
            <div class="header-left">
                <button class="menu-btn" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>รายการทั้งหมด</h1>
            </div>
            <div class="header-right">
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-info">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_info['full_name'] ?? 'Admin'); ?>&background=9d7cd8&color=fff" alt="Avatar" class="avatar">
                    <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($user_info['full_name'] ?? 'Admin'); ?></span>
                        <span class="user-role">ผู้ดูแลระบบ</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="dashboard-container">
            <!-- Filter -->
            <div class="filter-bar">
                <form class="filter-row" method="GET">
                    <div class="filter-item">
                        <label>ค้นหา</label>
                        <input type="text" name="search" placeholder="ค้นหารายการ, ผู้ใช้, หมวดหมู่..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-item">
                        <label>ประเภท</label>
                        <select name="type">
                            <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                            <option value="income" <?php echo $type_filter === 'income' ? 'selected' : ''; ?>>รายรับ</option>
                            <option value="expense" <?php echo $type_filter === 'expense' ? 'selected' : ''; ?>>รายจ่าย</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>ผู้ใช้</label>
                        <select name="user">
                            <option value="all">ทุกคน</option>
                            <?php while ($u = $users->fetch_assoc()): ?>
                            <option value="<?php echo $u['user_id']; ?>" <?php echo $user_filter == $u['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['username']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter"><i class="fas fa-search"></i> ค้นหา</button>
                </form>

                <div class="summary-bar">
                    <div class="summary-box income">
                        <i class="fas fa-arrow-up"></i> รายรับทั้งหมด: ฿<?php echo number_format($summary['total_income'], 2); ?>
                    </div>
                    <div class="summary-box expense">
                        <i class="fas fa-arrow-down"></i> รายจ่ายทั้งหมด: ฿<?php echo number_format($summary['total_expense'], 2); ?>
                    </div>
                    <div class="summary-box balance">
                        <i class="fas fa-wallet"></i> ยอดคงเหลือ: ฿<?php echo number_format($summary['total_income'] - $summary['total_expense'], 2); ?>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> รายการทั้งหมด (<?php echo $transactions->num_rows; ?> รายการ)</h3>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>ผู้ใช้</th>
                                <th>หมวดหมู่</th>
                                <th>รายละเอียด</th>
                                <th>ประเภท</th>
                                <th>จำนวน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($transactions->num_rows > 0): ?>
                                <?php while ($trans = $transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($trans['username']); ?>&background=random" alt="Avatar" class="table-avatar">
                                            <span><?php echo htmlspecialchars($trans['username']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($trans['category_name']); ?></td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($trans['description'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $trans['transaction_type'] === 'income' ? 'badge-income' : 'badge-expense'; ?>">
                                            <?php echo $trans['transaction_type'] === 'income' ? 'รายรับ' : 'รายจ่าย'; ?>
                                        </span>
                                    </td>
                                    <td class="<?php echo $trans['transaction_type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                        ฿<?php echo number_format($trans['amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-inbox" style="font-size: 48px; color: var(--text-gray); opacity: 0.3;"></i>
                                        <p style="margin-top: 15px; color: var(--text-gray);">ไม่พบรายการ</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../script/dashboard.js"></script>
    <script>
        // Sidebar Toggle for Admin Panel
        const adminToggleBtn = document.getElementById('toggleSidebar');
        const adminSidebar = document.getElementById('sidebar');
        
        if (adminToggleBtn && adminSidebar) {
            adminToggleBtn.addEventListener('click', () => {
                adminSidebar.classList.toggle('active');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        if (adminSidebar && adminToggleBtn) {
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    if (!adminSidebar.contains(e.target) && !adminToggleBtn.contains(e.target)) {
                        adminSidebar.classList.remove('active');
                    }
                }
            });
        }
        
        function confirmLogout() {
            if (confirm('คุณต้องการออกจากระบบหรือไม่?')) {
                window.location.href = '../auth.php?action=logout';
            }
        }
    </script>
</body>
</html>
