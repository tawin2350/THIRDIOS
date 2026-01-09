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
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการบัญชี - THIRDIOS</title>
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
                <li class="nav-item">
                    <a href="transactions.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        <span class="nav-text">รายการทั้งหมด</span>
                    </a>
                </li>
                <li class="nav-item active">
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
                <h1>จัดการบัญชี - THIRDIOS</h1>
            </div>
            <div class="header-right">
                <button class="theme-toggle" id="themeToggle" title="สลับธีม">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </header>

        <!-- Profile Content -->
        <div class="dashboard-container">
            <div class="profile-grid">
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h2><?php echo htmlspecialchars($user_info['full_name']); ?></h2>
                        <p><?php echo htmlspecialchars($user_info['email']); ?></p>
                        <span class="role-badge"><?php echo $user_info['role'] === 'admin' ? 'ผู้ดูแลระบบ' : 'ผู้ใช้งาน'; ?></span>
                    </div>
                    
                    <div class="profile-info">
                        <div class="info-item">
                            <i class="fas fa-calendar-plus"></i>
                            <div>
                                <label>สมัครสมาชิกเมื่อ</label>
                                <span><?php echo date('d/m/Y', strtotime($user_info['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-sign-in-alt"></i>
                            <div>
                                <label>เข้าสู่ระบบล่าสุด</label>
                                <span><?php echo $user_info['last_login'] ? date('d/m/Y H:i', strtotime($user_info['last_login'])) : 'ไม่พบข้อมูล'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="form-page-card">
                    <div class="form-page-header">
                        <i class="fas fa-key"></i>
                        <h2>เปลี่ยนรหัสผ่าน</h2>
                        <p>รักษาความปลอดภัยของบัญชีคุณ</p>
                    </div>
                    
                    <form id="changePasswordForm" class="form-page">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> รหัสผ่านปัจจุบัน</label>
                            <input type="password" name="old_password" required placeholder="กรอกรหัสผ่านปัจจุบัน">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> รหัสผ่านใหม่</label>
                            <input type="password" name="new_password" required placeholder="กรอกรหัสผ่านใหม่" minlength="6">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="confirm_password" required placeholder="กรอกรหัสผ่านใหม่อีกครั้ง" minlength="6">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save"></i> เปลี่ยนรหัสผ่าน
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="script/dashboard.js"></script>
    <script>
        // Handle change password form
        document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('.btn-submit');
            
            // Validate passwords match
            if (formData.get('new_password') !== formData.get('confirm_password')) {
                showToast('error', 'ข้อผิดพลาด', 'รหัสผ่านใหม่ไม่ตรงกัน');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...';
            
            try {
                const response = await fetch('auth.php?action=change_password', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> เปลี่ยนรหัสผ่าน';
                
                if (result.success) {
                    showToast('success', 'สำเร็จ!', result.message);
                    e.target.reset();
                } else {
                    showToast('error', 'เกิดข้อผิดพลาด', result.message);
                }
            } catch (error) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> เปลี่ยนรหัสผ่าน';
                showToast('error', 'เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
            }
        });
    </script>
</body>
</html>
