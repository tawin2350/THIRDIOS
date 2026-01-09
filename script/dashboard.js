// Sidebar Toggle Functionality
const sidebar = document.getElementById('sidebar');
const logoToggle = document.getElementById('logoToggle');
const menuBtn = document.getElementById('menuBtn') || document.getElementById('toggleSidebar');
const mainContent = document.getElementById('mainContent');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const themeToggle = document.getElementById('themeToggle');

// Dark Mode Toggle
const savedTheme = localStorage.getItem('theme') || 'light';
if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
}

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        
        // Animation
        themeToggle.style.transform = 'rotate(360deg) scale(1.2)';
        setTimeout(() => {
            themeToggle.style.transform = 'rotate(0deg) scale(1)';
        }, 300);
    });
}

// Toggle Sidebar on Desktop
if (logoToggle) {
    logoToggle.addEventListener('click', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.toggle('collapsed');
            
            // Add bounce animation to logo
            logoToggle.style.transform = 'scale(0.9)';
            setTimeout(() => {
                logoToggle.style.transform = 'scale(1)';
            }, 100);
        } else {
            // On mobile, close sidebar
            sidebar.classList.remove('active');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        }
    });
}

// Toggle Sidebar on Mobile
if (menuBtn) {
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
    });
}

// Close sidebar when clicking overlay
if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
    });
}

// Close sidebar when clicking outside on mobile
if (sidebar && menuBtn) {
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('active');
                if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            }
        }
    });
}

// Toast Notification
function showToast(type, title, message, duration = 3000) {
    const toast = document.getElementById('toast');
    
    toast.className = 'toast';
    toast.classList.add('show', type);
    
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

// Add Transaction Modal
function showAddModal(type) {
    const modal = document.getElementById('addModal');
    const typeInput = document.getElementById('transactionType');
    const modalTitle = document.getElementById('modalTitle');
    const categorySelect = document.getElementById('categorySelect');
    
    typeInput.value = type;
    modalTitle.textContent = type === 'income' ? 'เพิ่มรายรับ' : 'เพิ่มรายจ่าย';
    
    // Filter categories by type
    categorySelect.innerHTML = '<option value="">-- เลือกหมวดหมู่ --</option>';
    categories.filter(cat => cat.category_type === type).forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.category_id;
        option.textContent = cat.category_name;
        categorySelect.appendChild(option);
    });
    
    modal.classList.add('show');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    const modal = document.getElementById('addModal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        const form = document.getElementById('addTransactionForm');
        if (form) form.reset();
    }
}

// Handle Add Transaction Form (ถ้ามี modal)
const addTransactionForm = document.getElementById('addTransactionForm');
if (addTransactionForm) {
    addTransactionForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const submitBtn = e.target.querySelector('.btn-submit');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...';
        
        try {
            const response = await fetch('api.php?action=add_transaction', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> บันทึก';
            
            if (result.success) {
                showToast('success', 'สำเร็จ!', result.message);
                closeAddModal();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('error', 'เกิดข้อผิดพลาด', result.message);
            }
        } catch (error) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> บันทึก';
            showToast('error', 'เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
        }
    });
}

// Delete Transaction
async function deleteTransaction(id) {
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?')) {
        return;
    }
    
    try {
        const response = await fetch(`api.php?action=delete_transaction&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'สำเร็จ!', result.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', 'เกิดข้อผิดพลาด', result.message);
        }
    } catch (error) {
        showToast('error', 'เกิดข้อผิดพลาด', 'ไม่สามารถลบรายการได้');
    }
}

// Logout
function confirmLogout() {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการออกจากระบบ?')) {
        window.location.href = 'auth.php?action=logout';
    }
}

// Show All Transactions (placeholder)
function showAllTransactions() {
    showToast('info', 'กำลังพัฒนา', 'ฟีเจอร์นี้กำลังอยู่ในระหว่างการพัฒนา');
}

// Show Profile Modal (placeholder)
function showProfileModal() {
    showToast('info', 'กำลังพัฒนา', 'ฟีเจอร์นี้กำลังอยู่ในระหว่างการพัฒนา');
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    const modal = document.getElementById('addModal');
    if (modal && e.target === modal) {
        closeAddModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAddModal();
    }
});

// Chart Drawing
const canvas = document.getElementById('salesChart');
if (canvas && chartData.length > 0) {
    const ctx = canvas.getContext('2d');
    
    canvas.width = canvas.parentElement.offsetWidth;
    canvas.height = 300;
    
    const padding = 40;
    const chartWidth = canvas.width - padding * 2;
    const chartHeight = canvas.height - padding * 2;
    const barWidth = chartWidth / chartData.length;
    
    // Find max value for scaling
    let maxValue = 0;
    chartData.forEach(data => {
        maxValue = Math.max(maxValue, parseFloat(data.income), parseFloat(data.expense));
    });
    
    // Draw chart
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw grid lines
    ctx.strokeStyle = 'rgba(0, 0, 0, 0.05)';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 5; i++) {
        const y = padding + (chartHeight / 5) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(canvas.width - padding, y);
        ctx.stroke();
    }
    
    // Draw bars
    chartData.forEach((data, index) => {
        const x = padding + (barWidth * index);
        const incomeHeight = (parseFloat(data.income) / maxValue) * chartHeight;
        const expenseHeight = (parseFloat(data.expense) / maxValue) * chartHeight;
        
        // Income bar (green)
        ctx.fillStyle = '#10b981';
        ctx.fillRect(x + barWidth * 0.15, canvas.height - padding - incomeHeight, barWidth * 0.35, incomeHeight);
        
        // Expense bar (red)
        ctx.fillStyle = '#ef4444';
        ctx.fillRect(x + barWidth * 0.5, canvas.height - padding - expenseHeight, barWidth * 0.35, expenseHeight);
        
        // Labels
        ctx.fillStyle = '#6b7280';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(thaiMonths[data.month], x + barWidth / 2, canvas.height - 10);
    });
    
    // Legend
    ctx.fillStyle = '#10b981';
    ctx.fillRect(20, 20, 15, 15);
    ctx.fillStyle = '#1f2937';
    ctx.font = '12px Arial';
    ctx.textAlign = 'left';
    ctx.fillText('รายรับ', 40, 32);
    
    ctx.fillStyle = '#ef4444';
    ctx.fillRect(100, 20, 15, 15);
    ctx.fillText('รายจ่าย', 120, 32);
}


// Nav Item Animation
const navItems = document.querySelectorAll('.nav-item');
navItems.forEach((item, index) => {
    item.style.animation = `fadeInLeft 0.5s ease ${index * 0.1}s both`;
});

// Add ripple effect to nav links
navItems.forEach(item => {
    const link = item.querySelector('.nav-link');
    
    link.addEventListener('click', function(e) {
        // Remove active class from all items
        navItems.forEach(nav => nav.classList.remove('active'));
        // Add active class to clicked item
        item.classList.add('active');
        
        // Create ripple effect
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        this.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
});

// Add ripple CSS dynamically
const style = document.createElement('style');
style.textContent = `
    .nav-link {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.4);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);

// Category Cards Hover Effect with Particles
const categoryCards = document.querySelectorAll('.category-card');
categoryCards.forEach(card => {
    card.addEventListener('mouseenter', function(e) {
        createParticles(this);
    });
});

function createParticles(element) {
    const colors = ['#ff6b9d', '#5eb0ef', '#9d7cd8', '#ffa06e'];
    
    for (let i = 0; i < 5; i++) {
        const particle = document.createElement('div');
        particle.style.position = 'absolute';
        particle.style.width = '5px';
        particle.style.height = '5px';
        particle.style.borderRadius = '50%';
        particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.pointerEvents = 'none';
        particle.style.animation = 'particle-float 1s ease-out forwards';
        
        element.appendChild(particle);
        
        setTimeout(() => {
            particle.remove();
        }, 1000);
    }
}

// Add particle animation
const particleStyle = document.createElement('style');
particleStyle.textContent = `
    @keyframes particle-float {
        0% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translateY(-50px) scale(0);
            opacity: 0;
        }
    }
`;
document.head.appendChild(particleStyle);

// Smooth scroll animation for cards
const cards = document.querySelectorAll('.welcome-card, .stats-card, .category-card, .chart-card');
cards.forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
});

const cardObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.transition = 'all 0.6s ease';
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, index * 100);
        }
    });
}, { threshold: 0.1 });

cards.forEach(card => {
    cardObserver.observe(card);
});

// Resize chart on window resize
window.addEventListener('resize', () => {
    const canvas = document.getElementById('salesChart');
    if (canvas && chartData.length > 0) {
        canvas.width = canvas.parentElement.offsetWidth;
        const ctx = canvas.getContext('2d');
        
        const padding = 40;
        const chartWidth = canvas.width - padding * 2;
        const chartHeight = canvas.height - padding * 2;
        const barWidth = chartWidth / chartData.length;
        
        // Find max value for scaling
        let maxValue = 0;
        chartData.forEach(data => {
            maxValue = Math.max(maxValue, parseFloat(data.income), parseFloat(data.expense));
        });
        
        // Redraw chart
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw grid lines
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 5; i++) {
            const y = padding + (chartHeight / 5) * i;
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(canvas.width - padding, y);
            ctx.stroke();
        }
        
        // Draw bars
        chartData.forEach((data, index) => {
            const x = padding + (barWidth * index);
            const incomeHeight = (parseFloat(data.income) / maxValue) * chartHeight;
            const expenseHeight = (parseFloat(data.expense) / maxValue) * chartHeight;
            
            // Income bar (green)
            ctx.fillStyle = '#10b981';
            ctx.fillRect(x + barWidth * 0.15, canvas.height - padding - incomeHeight, barWidth * 0.35, incomeHeight);
            
            // Expense bar (red)
            ctx.fillStyle = '#ef4444';
            ctx.fillRect(x + barWidth * 0.5, canvas.height - padding - expenseHeight, barWidth * 0.35, expenseHeight);
            
            // Labels
            ctx.fillStyle = '#6b7280';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(thaiMonths[data.month], x + barWidth / 2, canvas.height - 10);
        });
        
        // Legend
        ctx.fillStyle = '#10b981';
        ctx.fillRect(20, 20, 15, 15);
        ctx.fillStyle = '#1f2937';
        ctx.font = '12px Arial';
        ctx.textAlign = 'left';
        ctx.fillText('รายรับ', 40, 32);
        
        ctx.fillStyle = '#ef4444';
        ctx.fillRect(100, 20, 15, 15);
        ctx.fillText('รายจ่าย', 120, 32);
    }
});
