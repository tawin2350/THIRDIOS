// Toggle Password Visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Toast Notification
function showToast(type, title, message, duration = 3000) {
    const toast = document.getElementById('toast');
    
    // Reset classes
    toast.className = 'toast';
    toast.classList.add('show', type);
    
    // Icon based on type
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas ${icons[type]}"></i>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    setTimeout(() => {
        closeToast();
    }, duration);
}

function closeToast() {
    const toast = document.getElementById('toast');
    toast.classList.remove('show');
}

// Login Form Handler
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = loginForm.querySelector('.btn-submit');
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('auth.php?action=login', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            
            if (result.success) {
                showToast('success', 'สำเร็จ!', result.message);
                setTimeout(() => {
                    if (result.role === 'admin') {
                        window.location.href = 'admin/index.php';
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                }, 1500);
            } else {
                showToast('error', 'เกิดข้อผิดพลาด', result.message);
            }
        } catch (error) {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            showToast('error', 'เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
        }
    });
}

// Register Form Handler
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const password = document.getElementById('reg_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const username = document.getElementById('reg_username').value;
        
        // Validate username
        if (username.length < 4 || username.length > 20) {
            showToast('warning', 'คำเตือน', 'ชื่อผู้ใช้ต้องมีความยาว 4-20 ตัวอักษร');
            return;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            showToast('warning', 'คำเตือน', 'ชื่อผู้ใช้ต้องเป็นอักษรภาษาอังกฤษ ตัวเลข หรือ _ เท่านั้น');
            return;
        }
        
        // Validate password
        if (password.length < 6) {
            showToast('warning', 'คำเตือน', 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
            return;
        }
        
        if (password !== confirmPassword) {
            showToast('warning', 'คำเตือน', 'รหัสผ่านไม่ตรงกัน');
            return;
        }
        
        const submitBtn = registerForm.querySelector('.btn-submit');
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        const formData = new FormData(registerForm);
        
        try {
            const response = await fetch('auth.php?action=register', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            
            if (result.success) {
                showToast('success', 'สำเร็จ!', result.message);
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                showToast('error', 'เกิดข้อผิดพลาด', result.message);
            }
        } catch (error) {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            showToast('error', 'เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
        }
    });
}

// Form Input Animation
document.querySelectorAll('.form-group input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'translateY(-2px)';
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'translateY(0)';
    });
});

// Terms Modal Functions
function openTermsModal(event) {
    event.preventDefault();
    const modal = document.getElementById('termsModal');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeTermsModal() {
    const modal = document.getElementById('termsModal');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

function acceptTerms() {
    const checkbox = document.querySelector('input[name="agree"]');
    if (checkbox) {
        checkbox.checked = true;
    }
    closeTermsModal();
    showToast('success', 'ยอมรับแล้ว', 'คุณได้ยอมรับเงื่อนไขการใช้งานแล้ว');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('termsModal');
    if (event.target === modal) {
        closeTermsModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('termsModal');
        if (modal && modal.classList.contains('show')) {
            closeTermsModal();
        }
    }
});
