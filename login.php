<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - THIRDIOS</title>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-container">
                    <img src="images/logo.PNG" alt="THIRDIOS Logo" class="logo">
                    <h1>THIRDIOS</h1>
                </div>
                <p>ระบบวิเคราะห์รายรับรายจ่ายส่วนบุคคล</p>
            </div>

            <form id="loginForm" class="auth-form">
                <h2>เข้าสู่ระบบ</h2>
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        ชื่อผู้ใช้หรืออีเมล
                    </label>
                    <input type="text" id="username" name="username" required placeholder="กรอกชื่อผู้ใช้หรืออีเมล">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        รหัสผ่าน
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required placeholder="กรอกรหัสผ่าน">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>จดจำฉันไว้</span>
                    </label>
                    <a href="#" class="forgot-password">ลืมรหัสผ่าน?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i>
                    เข้าสู่ระบบ
                </button>

                <div class="form-footer">
                    <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
                </div>
            </form>
        </div>

        <div class="background-animation">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="script/auth.js"></script>
</body>
</html>
