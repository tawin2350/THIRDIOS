<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// ตรวจสอบการ login และต้องเป็น admin
require_login();

$user_id = $_SESSION['user_id'];
$user_info = get_user_info($user_id);

// ตรวจสอบว่าเป็น admin หรือไม่
if ($user_info['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// ดึงสถิติรวม
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admins,
    (SELECT COUNT(*) FROM transactions) as total_transactions,
    (SELECT SUM(amount) FROM transactions WHERE transaction_type = 'income') as total_income,
    (SELECT SUM(amount) FROM transactions WHERE transaction_type = 'expense') as total_expense";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// ดึงผู้ใช้ล่าสุด
$users_sql = "SELECT user_id, username, email, full_name, role, created_at, last_login 
              FROM users 
              ORDER BY created_at DESC 
              LIMIT 10";
$users_result = $conn->query($users_sql);

// ดึงรายการล่าสุด
$transactions_sql = "SELECT t.*, u.username, c.category_name 
                     FROM transactions t
                     LEFT JOIN users u ON t.user_id = u.user_id
                     LEFT JOIN categories c ON t.category_id = c.category_id
                     ORDER BY t.created_at DESC
                     LIMIT 10";
$transactions_result = $conn->query($transactions_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - THIRDIOS</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li class="nav-item active">
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
                <li class="nav-item">
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
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="menu-btn" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>แผงควบคุม Admin</h1>
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

        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>ผู้ใช้ทั้งหมด</p>
                    </div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_admins']); ?></h3>
                        <p>ผู้ดูแลระบบ</p>
                    </div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <div class="stat-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_transactions']); ?></h3>
                        <p>รายการทั้งหมด</p>
                    </div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stat-info">
                        <h3>฿<?php echo number_format($stats['total_income'] ?? 0, 2); ?></h3>
                        <p>รายรับทั้งหมด</p>
                    </div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stat-info">
                        <h3>฿<?php echo number_format($stats['total_expense'] ?? 0, 2); ?></h3>
                        <p>รายจ่ายทั้งหมด</p>
                    </div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0, #330867);">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>฿<?php echo number_format(($stats['total_income'] ?? 0) - ($stats['total_expense'] ?? 0), 2); ?></h3>
                        <p>ยอดคงเหลือรวม</p>
                    </div>
                </div>
            </div>

            <!-- Recent Users and Transactions -->
            <div class="admin-grid">
                <!-- Recent Users -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> ผู้ใช้ล่าสุด</h3>
                        <a href="users.php" class="btn-link">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>อีเมล</th>
                                    <th>สิทธิ์</th>
                                    <th>วันที่สมัคร</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=random" alt="Avatar" class="table-avatar">
                                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> รายการล่าสุด</h3>
                        <a href="transactions.php" class="btn-link">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ผู้ใช้</th>
                                    <th>หมวดหมู่</th>
                                    <th>ประเภท</th>
                                    <th>จำนวน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($trans = $transactions_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($trans['username']); ?></td>
                                    <td><?php echo htmlspecialchars($trans['category_name']); ?></td>
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
                            </tbody>
                        </table>
                    </div>
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
