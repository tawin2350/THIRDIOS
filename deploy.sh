#!/bin/bash

# THIRDIOS Deployment Script for EC2 Ubuntu
# Run this script on your EC2 instance

echo "=================================="
echo "THIRDIOS Deployment Script"
echo "=================================="

# Update system
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install Apache
echo "🌐 Installing Apache..."
sudo apt install apache2 -y

# Install PHP and extensions
echo "🐘 Installing PHP and extensions..."
sudo apt install php php-mysql php-mbstring php-xml php-curl libapache2-mod-php -y

# Install MySQL Client
echo "🗄️  Installing MySQL Client..."
sudo apt install mysql-client -y

# Install Git
echo "📚 Installing Git..."
sudo apt install git -y

# Enable Apache modules
echo "⚙️  Configuring Apache modules..."
sudo a2enmod rewrite
sudo a2enmod php8.1 2>/dev/null || sudo a2enmod php8.3 2>/dev/null || sudo a2enmod php

# Remove default files
echo "🗑️  Removing default Apache files..."
sudo rm -rf /var/www/html/*

# Clone repository
echo "📥 Cloning THIRDIOS from GitHub..."
cd /var/www/html
sudo git clone https://github.com/tawin2350/THIRDIOS.git .

# Set permissions
echo "🔐 Setting file permissions..."
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

# Configure Apache
echo "⚙️  Configuring Apache Virtual Host..."
sudo tee /etc/apache2/sites-available/000-default.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Restart Apache
echo "🔄 Restarting Apache..."
sudo systemctl restart apache2

# Enable Apache on boot
sudo systemctl enable apache2

# Get public IP
PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4)

echo ""
echo "=================================="
echo "✅ Deployment Complete!"
echo "=================================="
echo ""
echo "📍 Your website is now available at:"
echo "   http://$PUBLIC_IP"
echo ""
echo "⚠️  Important: Make sure Security Group allows HTTP (Port 80)"
echo ""
echo "Database Config:"
echo "   Host: ballast.proxy.rlwy.net"
echo "   Port: 32938"
echo "   User: root"
echo "   Database: railway"
echo ""
echo "Default Admin Login:"
echo "   Username: admin"
echo "   Password: admin123"
echo ""
