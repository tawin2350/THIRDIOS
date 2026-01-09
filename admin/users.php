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

// ดึงข้อมูลผู้ใช้ทั้งหมด
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? 'all';

$sql = "SELECT u.*, 
        COUNT(t.transaction_id) as transaction_count,
        COALESCE(SUM(CASE WHEN t.transaction_type = 'income' THEN t.amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN t.transaction_type = 'expense' THEN t.amount ELSE 0 END), 0) as total_expense
        FROM users u
        LEFT JOIN transactions t ON u.user_id = t.user_id
        WHERE 1=1";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%' OR u.full_name LIKE '%$search%')";
}

if ($role_filter !== 'all') {
    $role_filter = $conn->real_escape_string($role_filter);
    $sql .= " AND u.role = '$role_filter'";
}

$sql .= " GROUP BY u.user_id ORDER BY u.created_at DESC";
$users_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้ - THIRDIOS Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .search-bar input, .search-bar select {
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            flex: 1;
            min-width: 200px;
        }
        .search-bar button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo" id="logoToggle">
                <img src="../images/logo.png" alt="Third Logo">
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
                <li class="nav-item active">
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
        <header class="header">
            <div class="header-left">
                <button class="menu-btn" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>จัดการผู้ใช้</h1>
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
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> รายชื่อผู้ใช้ทั้งหมด</h3>
                </div>

                <!-- Search Bar -->
                <form class="search-bar" method="GET">
                    <input type="text" name="search" placeholder="ค้นหาชื่อผู้ใช้, อีเมล, ชื่อ-นามสกุล..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="role">
                        <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>ทุกสิทธิ์</option>
                        <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>ผู้ใช้ทั่วไป</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                    </select>
                    <button type="submit"><i class="fas fa-search"></i> ค้นหา</button>
                </form>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ชื่อผู้ใช้</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>อีเมล</th>
                                <th>สิทธิ์</th>
                                <th>จำนวนรายการ</th>
                                <th>รายรับ</th>
                                <th>รายจ่าย</th>
                                <th>วันที่สมัคร</th>
                                <th>เข้าสู่ระบบล่าสุด</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td>
                                    <div class="user-cell">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=random" alt="Avatar" class="table-avatar">
                                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($user['transaction_count']); ?></td>
                                <td class="text-success">฿<?php echo number_format($user['total_income'], 2); ?></td>
                                <td class="text-danger">฿<?php echo number_format($user['total_expense'], 2); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-'; ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['user_id'] != $user_id): ?>
                                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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

        function editUser(userId) {
            alert('ฟีเจอร์แก้ไขผู้ใช้กำลังพัฒนา');
        }

        function deleteUser(userId, username) {
            if (confirm(`คุณต้องการลบผู้ใช้ "${username}" หรือไม่?\n\nการลบจะลบรายการทั้งหมดของผู้ใช้ด้วย`)) {
                alert('ฟีเจอร์ลบผู้ใช้กำลังพัฒนา');
            }
        }
    </script>
</body>
</html>
