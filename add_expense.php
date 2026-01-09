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

// ดึงหมวดหมู่รายจ่าย
$sql_categories = "SELECT * FROM categories WHERE category_type = 'expense' ORDER BY category_name";
$categories_result = $conn->query($sql_categories);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มรายจ่าย - THIRDIOS</title>
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
                <li class="nav-item active">
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
                <h1>เพิ่มรายจ่าย - THIRDIOS</h1>
            </div>
            <div class="header-right">
                <button class="theme-toggle" id="themeToggle" title="สลับธีม">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </header>

        <!-- Form Content -->
        <div class="dashboard-container">
            <div class="form-page-card expense-page">
                <div class="form-page-header">
                    <i class="fas fa-minus-circle"></i>
                    <h2>เพิ่มรายจ่าย</h2>
                    <p>บันทึกรายจ่ายของคุณ</p>
                </div>
                
                <form id="addExpenseForm" class="form-page">
                    <input type="hidden" name="type" value="expense">
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> หมวดหมู่</label>
                        <select name="category_id" required>
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> จำนวนเงิน</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> รายละเอียด</label>
                        <textarea name="description" rows="4" placeholder="ระบุรายละเอียด (ถ้ามี)"></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> วันที่</label>
                        <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit expense-btn">
                            <i class="fas fa-save"></i> บันทึกรายจ่าย
                        </button>
                        <a href="dashboard.php" class="btn-cancel">
                            <i class="fas fa-times"></i> ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="loading-modal" id="loadingModal">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">กำลังประมวลผล...</div>
        </div>
    </div>

    <!-- Result Modal -->
    <div class="result-modal" id="resultModal">
        <div class="result-content">
            <div class="result-icon" id="resultIcon">
                <i class="fas fa-check"></i>
            </div>
            <div class="result-title" id="resultTitle">สำเร็จ!</div>
            <div class="result-message" id="resultMessage">บันทึกรายการเรียบร้อยแล้ว</div>
            <button class="result-btn" id="resultBtn" onclick="closeResultModal()">ตรวจสอบ</button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        const categories = <?php echo json_encode($categories); ?>;
    </script>
    <script src="script/dashboard.js"></script>
    <script>
        // Loading Modal Functions
        function showLoadingModal() {
            document.getElementById('loadingModal').classList.add('active');
        }

        function hideLoadingModal() {
            document.getElementById('loadingModal').classList.remove('active');
        }

        function showResultModal(success, message) {
            const resultModal = document.getElementById('resultModal');
            const resultIcon = document.getElementById('resultIcon');
            const resultTitle = document.getElementById('resultTitle');
            const resultMessage = document.getElementById('resultMessage');
            const resultBtn = document.getElementById('resultBtn');

            if (success) {
                resultIcon.className = 'result-icon success';
                resultIcon.innerHTML = '<i class="fas fa-check"></i>';
                resultTitle.className = 'result-title success';
                resultTitle.textContent = 'สำเร็จ!';
                resultBtn.className = 'result-btn success';
            } else {
                resultIcon.className = 'result-icon error';
                resultIcon.innerHTML = '<i class="fas fa-times"></i>';
                resultTitle.className = 'result-title error';
                resultTitle.textContent = 'เกิดข้อผิดพลาด!';
                resultBtn.className = 'result-btn error';
            }

            resultMessage.textContent = message;
            resultModal.classList.add('active');
        }

        function closeResultModal() {
            const resultModal = document.getElementById('resultModal');
            resultModal.classList.remove('active');
            // ถ้าสำเร็จให้กลับไปหน้า dashboard
            if (document.getElementById('resultTitle').classList.contains('success')) {
                window.location.href = 'dashboard.php';
            }
        }

        // Handle form submission
        document.getElementById('addExpenseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('.btn-submit');
            
            // แสดง Loading Modal
            showLoadingModal();
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('api.php?action=add_transaction', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // ซ่อน Loading Modal
                hideLoadingModal();
                submitBtn.disabled = false;
                
                // แสดง Result Modal
                if (result.success) {
                    showResultModal(true, result.message || 'บันทึกรายจ่ายเรียบร้อยแล้ว');
                } else {
                    showResultModal(false, result.message || 'ไม่สามารถบันทึกรายจ่ายได้');
                }
            } catch (error) {
                hideLoadingModal();
                submitBtn.disabled = false;
                showResultModal(false, 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
            }
        });
    </script>
</body>
</html>
