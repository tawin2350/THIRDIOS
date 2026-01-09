# THIRDIOS - EC2 Ubuntu Deployment Guide

## 🚀 Quick Deploy (วิธีง่าย)

### 1. เชื่อมต่อกับ EC2
```bash
ssh -i your-key.pem ubuntu@your-ec2-ip
```

### 2. รันคำสั่งเดียวจบ
```bash
curl -o deploy.sh https://raw.githubusercontent.com/tawin2350/THIRDIOS/main/deploy.sh && chmod +x deploy.sh && sudo ./deploy.sh
```

---

## 📋 Manual Deploy (วิธีละเอียด)

### Step 1: Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### Step 2: Install Apache
```bash
sudo apt install apache2 -y
```

### Step 3: Install PHP
```bash
sudo apt install php php-mysql php-mbstring php-xml php-curl libapache2-mod-php -y
```

### Step 4: Install MySQL Client
```bash
sudo apt install mysql-client -y
```

### Step 5: Install Git
```bash
sudo apt install git -y
```

### Step 6: Configure Apache
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Step 7: Clone Project
```bash
cd /var/www/html
sudo rm -rf *
sudo git clone https://github.com/tawin2350/THIRDIOS.git .
```

### Step 8: Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### Step 9: Configure Virtual Host
```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

แก้ไขเป็น:
```apache
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### Step 10: Restart Apache
```bash
sudo systemctl restart apache2
sudo systemctl enable apache2
```

### Step 11: เปิด Port ใน AWS Security Group
1. ไปที่ **EC2 Dashboard** > **Security Groups**
2. เลือก Security Group ที่ใช้กับ instance
3. **Inbound Rules** > **Edit inbound rules**
4. **Add rule**:
   - Type: HTTP
   - Port: 80
   - Source: 0.0.0.0/0
5. **Save rules**

### Step 12: ทดสอบ
```bash
# ดู Public IP
curl http://169.254.169.254/latest/meta-data/public-ipv4

# เปิดเว็บบราวเซอร์
http://your-ec2-public-ip
```

---

## 🔧 Database Configuration

Database ถูก config ไว้แล้วที่:
- **Host**: ballast.proxy.rlwy.net
- **Port**: 32938
- **User**: root
- **Password**: ELvjVbaLLkpEcnYbGbwkkPekEODGlKds
- **Database**: railway

---

## 🔑 Default Login

**Admin Account:**
- Username: `admin`
- Password: `admin123`

---

## 📝 หลังติดตั้งเสร็จ

### Update โค้ดใหม่
```bash
cd /var/www/html
sudo git pull origin main
sudo systemctl restart apache2
```

### ดู Apache Error Log
```bash
sudo tail -f /var/log/apache2/error.log
```

### ดู Apache Access Log
```bash
sudo tail -f /var/log/apache2/access.log
```

### รีสตาร์ท Apache
```bash
sudo systemctl restart apache2
```

### เช็คสถานะ Apache
```bash
sudo systemctl status apache2
```

---

## ⚠️ Troubleshooting

### ถ้าเว็บไม่ขึ้น
1. เช็ค Apache status: `sudo systemctl status apache2`
2. เช็ค error log: `sudo tail -f /var/log/apache2/error.log`
3. เช็ค permissions: `ls -la /var/www/html`
4. เช็ค Security Group: ต้องเปิด Port 80

### ถ้า Database Error
1. เช็คว่า Railway database ยังทำงานอยู่
2. เช็คว่า config.php ถูกต้อง
3. ทดสอบเชื่อมต่อ:
```bash
mysql -h ballast.proxy.rlwy.net -P 32938 -u root -p railway
```

---

## 🌟 Optional: ติดตั้ง SSL (HTTPS)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL Certificate
sudo certbot --apache -d your-domain.com

# Auto-renewal
sudo certbot renew --dry-run
```

---

**Need help?** Check logs or contact support!
