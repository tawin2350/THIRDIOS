-- สร้างฐานข้อมูล THIRDIOS
CREATE DATABASE IF NOT EXISTS thirdios_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thirdios_db;

-- ตาราง users (ผู้ใช้งาน)
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตาราง categories (หมวดหมู่รายรับ-รายจ่าย)
CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    category_type ENUM('income', 'expense') NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-circle',
    color VARCHAR(20) DEFAULT '#667eea',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตาราง transactions (รายการรับ-จ่าย)
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    transaction_type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตาราง announcements (ประกาศจาก Admin)
CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตาราง settings (การตั้งค่าระบบ)
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตาราง activity_logs (บันทึกการใช้งาน)
CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- สร้าง Admin Account (username: admin, password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@thirdios.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin');

-- ข้อมูลหมวดหมู่เริ่มต้น
INSERT INTO categories (category_name, category_type, icon, color, created_by) VALUES
('เงินเดือน', 'income', 'fa-money-bill-wave', '#10b981', 1),
('ขายของออนไลน์', 'income', 'fa-shopping-cart', '#3b82f6', 1),
('โบนัส', 'income', 'fa-gift', '#8b5cf6', 1),
('รายได้อื่นๆ', 'income', 'fa-coins', '#14b8a6', 1),
('อาหาร', 'expense', 'fa-utensils', '#ef4444', 1),
('ค่าเดินทาง', 'expense', 'fa-car', '#f59e0b', 1),
('ค่าบ้าน', 'expense', 'fa-home', '#ec4899', 1),
('ช้อปปิ้ง', 'expense', 'fa-shopping-bag', '#a855f7', 1),
('ค่าใช้จ่ายอื่นๆ', 'expense', 'fa-receipt', '#6366f1', 1);

-- ข้อมูลทดสอบ (Transaction ตัวอย่าง)
INSERT INTO transactions (user_id, category_id, transaction_type, amount, description, transaction_date) VALUES
(1, 1, 'income', 35000.00, 'เงินเดือนประจำเดือนมกราคม', '2569-01-05'),
(1, 5, 'expense', 3500.00, 'ซื้อของกินตลาดนัด', '2569-01-06'),
(1, 6, 'expense', 1200.00, 'ค่าน้ำมันรถ', '2569-01-07'),
(1, 2, 'income', 5000.00, 'ขายของออนไลน์', '2569-01-08'),
(1, 7, 'expense', 8000.00, 'ค่าเช่าบ้าน', '2569-01-01'),
(1, 8, 'expense', 2500.00, 'ซื้อเสื้อผ้า', '2569-01-09');

-- ประกาศตัวอย่าง
INSERT INTO announcements (title, content, type, created_by) VALUES
('ยินดีต้อนรับสู่ THIRDIOS', 'ระบบจัดการรายรับ-รายจ่ายส่วนบุคคล ช่วยให้คุณติดตามการเงินได้ง่ายขึ้น', 'success', 1),
('อัพเดทระบบ', 'เพิ่มฟีเจอร์กราฟวิเคราะห์รายเดือน และรายงานสรุปประจำปี', 'info', 1);

-- สร้าง Index เพื่อเพิ่มประสิทธิภาพ
CREATE INDEX idx_user_id ON transactions(user_id);
CREATE INDEX idx_transaction_date ON transactions(transaction_date);
CREATE INDEX idx_category_type ON categories(category_type);
CREATE INDEX idx_user_role ON users(role);
