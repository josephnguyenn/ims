# 🚀 Server Deployment Guide for doan.tgndigital.xyz

## Quick Deployment Steps

### 1. Connect to Your Server

```bash
ssh user@doan.tgndigital.xyz
# OR
ssh user@your-server-ip
```

### 2. Install Required Software (if not already installed)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1+
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd

# Install MySQL
sudo apt install -y mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo apt install -y nginx

# Install Git
sudo apt install -y git
```

### 3. Clone Your Repository

```bash
# Navigate to web directory
cd /var/www

# Clone from GitHub
sudo git clone -b development https://github.com/josephnguyenn/ims.git
cd ims

# Set permissions
sudo chown -R www-data:www-data /var/www/ims
sudo chmod -R 755 /var/www/ims
```

### 4. Install Dependencies

```bash
cd /var/www/ims
composer install --optimize-autoloader --no-dev
```

### 5. Setup Environment

```bash
# Copy production environment
cp .env.production .env

# Generate application key
php artisan key:generate

# Edit environment file
sudo nano .env
```

Update these values in `.env`:
```env
APP_URL=https://doan.tgndigital.xyz
DB_HOST=localhost
DB_DATABASE=ims_production
DB_USERNAME=ims_user
DB_PASSWORD=your_secure_password
```

### 6. Create Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Run these SQL commands:
CREATE DATABASE ims_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ims_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON ims_production.* TO 'ims_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import your database (upload the SQL file first)
mysql -u ims_user -p ims_production < /path/to/ims_backup_20251111_094717.sql
```

### 7. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/ims
sudo chmod -R 755 /var/www/ims
sudo chmod -R 775 /var/www/ims/storage
sudo chmod -R 775 /var/www/ims/bootstrap/cache
```

### 8. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/doan.tgndigital.xyz
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name doan.tgndigital.xyz www.doan.tgndigital.xyz;
    root /var/www/ims/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/doan.tgndigital.xyz /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 10. Setup SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d doan.tgndigital.xyz -d www.doan.tgndigital.xyz

# Certbot will automatically configure HTTPS and redirect
```

### 11. Test Your Application

Visit: `https://doan.tgndigital.xyz/ims-dashboard/login.php`

Test credentials from your database.

## 🔄 Quick Update Commands (for future updates)

```bash
cd /var/www/ims
sudo git pull origin development
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.1-fpm
```

## 🔒 Security Checklist

After deployment:
- [ ] Verify `APP_DEBUG=false` in `.env`
- [ ] SSL certificate is active (HTTPS working)
- [ ] Strong database password set
- [ ] Firewall configured: `sudo ufw enable`
- [ ] Only necessary ports open: 22 (SSH), 80 (HTTP), 443 (HTTPS)

## 📊 Performance Tuning

### Enable OPcache

```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

Add:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.1-fpm
```

## 🔧 Troubleshooting

### Check Logs
```bash
# Laravel logs
tail -f /var/www/ims/storage/logs/laravel.log

# Nginx error logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.1-fpm.log
```

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/ims
sudo chmod -R 755 /var/www/ims
sudo chmod -R 775 /var/www/ims/storage
sudo chmod -R 775 /var/www/ims/bootstrap/cache
```

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📱 Access Points

After successful deployment:
- **Dashboard**: https://doan.tgndigital.xyz/ims-dashboard/login.php
- **API**: https://doan.tgndigital.xyz/api
- **POS**: https://doan.tgndigital.xyz/ims-dashboard/pos/login.php

## 🎉 You're Done!

Your IMS application is now live on https://doan.tgndigital.xyz
