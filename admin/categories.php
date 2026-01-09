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

// ดึงหมวดหมู่ทั้งหมด
$categories_sql = "SELECT c.*, COUNT(t.transaction_id) as usage_count 
                   FROM categories c
                   LEFT JOIN transactions t ON c.category_id = t.category_id
                   GROUP BY c.category_id
                   ORDER BY c.category_type, c.category_name";
$categories = $conn->query($categories_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหมวดหมู่ - THIRDIOS Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-grid-admin {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .category-item {
            background: var(--bg-white);
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            position: relative;
        }
        .category-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        .category-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .category-icon-box {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        .category-icon-box.income {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .category-icon-box.expense {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        .category-info h4 {
            font-size: 18px;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        .category-info p {
            font-size: 13px;
            color: var(--text-gray);
        }
        .category-stats {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            margin-top: 15px;
        }
        .stat-label {
            font-size: 12px;
            color: var(--text-gray);
        }
        .stat-value {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
        }
        .btn-add {
            padding: 14px 30px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
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
                <li class="nav-item">
                    <a href="transactions.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        <span class="nav-text">รายการทั้งหมด</span>
                    </a>
                </li>
                <li class="nav-item active">
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
                <h1>จัดการหมวดหมู่</h1>
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
                    <h3><i class="fas fa-tags"></i> หมวดหมู่ทั้งหมด</h3>
                    <button class="btn-add" onclick="alert('ฟีเจอร์เพิ่มหมวดหมู่กำลังพัฒนา')">
                        <i class="fas fa-plus"></i> เพิ่มหมวดหมู่ใหม่
                    </button>
                </div>

                <div class="category-grid-admin">
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                    <div class="category-item">
                        <div class="category-header">
                            <div class="category-icon-box <?php echo $cat['category_type']; ?>">
                                <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                            </div>
                            <div class="category-info">
                                <h4><?php echo htmlspecialchars($cat['category_name']); ?></h4>
                                <p>
                                    <span class="badge <?php echo $cat['category_type'] === 'income' ? 'badge-income' : 'badge-expense'; ?>">
                                        <?php echo $cat['category_type'] === 'income' ? 'รายรับ' : 'รายจ่าย'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="category-stats">
                            <div>
                                <div class="stat-label">จำนวนการใช้งาน</div>
                                <div class="stat-value"><?php echo number_format($cat['usage_count']); ?> ครั้ง</div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
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
