# IMS - Deployment Guide

## 📦 Prerequisites

- Linux server (Ubuntu 20.04+ recommended) or cPanel hosting
- PHP 8.1+
- MySQL 8.0+
- Composer
- Web server (Apache/Nginx)

## 🚀 Deployment Steps

### Option 1: Manual Deployment (VPS/Dedicated Server)

#### 1. Prepare Your Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd unzip nginx mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 2. Upload Project Files

```bash
# On your local machine, create deployment package
cd c:\xampp\htdocs\tappomarket\ims
git init
git add .
git commit -m "Initial commit for deployment"

# Or compress and upload via FTP/SCP
# zip -r ims.zip . -x "*.git*" "node_modules/*" "vendor/*"

# On server, clone or extract
cd /var/www
sudo git clone <your-repo-url> ims
# OR
sudo unzip ims.zip -d ims
sudo chown -R www-data:www-data ims
sudo chmod -R 755 ims
```

#### 3. Install Dependencies

```bash
cd /var/www/ims
composer install --optimize-autoloader --no-dev
```

#### 4. Configure Environment

```bash
# Copy production environment file
cp .env.production .env

# Edit with your production settings
nano .env

# Update these values:
# - APP_URL=https://yourdomain.com
# - DB_HOST=localhost
# - DB_DATABASE=tappomarket_ims
# - DB_USERNAME=your_db_user
# - DB_PASSWORD=your_secure_password
# - APP_DEBUG=false
# - APP_ENV=production
```

#### 5. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/ims
sudo chmod -R 755 /var/www/ims
sudo chmod -R 775 /var/www/ims/storage
sudo chmod -R 775 /var/www/ims/bootstrap/cache
```

#### 6. Create Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE tappomarket_ims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ims_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON tappomarket_ims.* TO 'ims_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import your database
mysql -u ims_user -p tappomarket_ims < /path/to/your/database_backup.sql
```

#### 7. Optimize Laravel

```bash
cd /var/www/ims

# Generate app key (if not set)
php artisan key:generate

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if any pending)
php artisan migrate --force
```

#### 8. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/ims
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/ims/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/ims /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 9. Setup SSL (Optional but Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

### Option 2: cPanel Deployment

#### 1. Prepare Files

On your local machine:
```bash
cd c:\xampp\htdocs\tappomarket\ims
composer install --optimize-autoloader --no-dev
```

Create a zip file excluding:
- .git
- node_modules
- vendor (will be regenerated)
- .env (will be created on server)

#### 2. Upload to cPanel

1. Login to cPanel
2. Go to File Manager
3. Navigate to `public_html` (or your domain folder)
4. Upload `ims.zip`
5. Extract the zip file
6. Delete the zip file

#### 3. Setup Database

1. Go to MySQL Databases in cPanel
2. Create a new database (e.g., `username_ims`)
3. Create a new user with a strong password
4. Add user to database with ALL PRIVILEGES
5. Use phpMyAdmin to import your database SQL file

#### 4. Configure Environment

1. In File Manager, navigate to your IMS folder
2. Copy `.env.production` to `.env`
3. Edit `.env` and update:
   - `APP_URL=https://yourdomain.com`
   - `DB_HOST=localhost`
   - `DB_DATABASE=username_ims`
   - `DB_USERNAME=username_dbuser`
   - `DB_PASSWORD=your_password`
   - `APP_DEBUG=false`

#### 5. Install Dependencies via SSH (or Terminal in cPanel)

```bash
cd public_html/ims
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 6. Setup Document Root

In cPanel, go to "Domains" and set the document root to:
```
public_html/ims/public
```

---

## 🔒 Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Change `APP_ENV=production`
- [ ] Use strong database passwords
- [ ] Setup SSL certificate (HTTPS)
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Enable firewall (UFW on Ubuntu)
- [ ] Regularly update dependencies (`composer update`)
- [ ] Setup automated backups for database

---

## 🔧 Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/ims
sudo chmod -R 755 /var/www/ims
sudo chmod -R 775 /var/www/ims/storage
sudo chmod -R 775 /var/www/ims/bootstrap/cache
```

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Connection Issues
- Check `.env` file for correct credentials
- Verify MySQL is running: `sudo systemctl status mysql`
- Test connection: `mysql -u username -p database_name`

### 500 Error
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug temporarily: `APP_DEBUG=true`
- Check Nginx/Apache error logs

---

## 📊 Performance Optimization

### Enable OPcache (if not already enabled)

Edit PHP configuration:
```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

Add/uncomment:
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

### Database Optimization

Already implemented:
- ✅ Indexes on frequently queried columns
- ✅ Pagination for large datasets
- ✅ Query optimization

### Enable Compression

In Nginx configuration, add:
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;
```

---

## 📱 Access Points After Deployment

- **Dashboard**: `https://yourdomain.com/ims-dashboard/login.php`
- **API**: `https://yourdomain.com/api`
- **POS**: `https://yourdomain.com/ims-dashboard/pos/login.php`

---

## 🔄 Updating Your Application

```bash
# Pull latest changes
cd /var/www/ims
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

---

## 📞 Support

If you encounter any issues during deployment, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Web server logs: `/var/log/nginx/error.log`
3. PHP logs: `/var/log/php8.1-fpm.log`

---

**Good luck with your deployment! 🚀**
