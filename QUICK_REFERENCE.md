# 📋 IMS Deployment Quick Reference

## 🎯 Your Setup

- **Domain**: doan.tgndigital.xyz
- **Repository**: https://github.com/josephnguyenn/ims
- **Branch**: development
- **Database Backup**: database_backup/ims_backup_20251111_094717.sql

## 🚀 Deployment Commands (On Server)

### Initial Deployment
```bash
cd /var/www
sudo git clone -b development https://github.com/josephnguyenn/ims.git
cd ims
composer install --optimize-autoloader --no-dev
cp .env.production .env
# Edit .env with your database credentials
php artisan key:generate
# Import database
mysql -u ims_user -p ims_production < /path/to/backup.sql
# Set permissions
sudo chown -R www-data:www-data /var/www/ims
sudo chmod -R 755 /var/www/ims
sudo chmod -R 775 /var/www/ims/storage
sudo chmod -R 775 /var/www/ims/bootstrap/cache
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Update Deployment
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

## 🌐 Access URLs

After deployment, access your application:
- **Dashboard**: https://doan.tgndigital.xyz/ims-dashboard/login.php
- **API**: https://doan.tgndigital.xyz/api
- **POS**: https://doan.tgndigital.xyz/ims-dashboard/pos/login.php

## 📝 Environment Variables (.env)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://doan.tgndigital.xyz

DB_HOST=localhost
DB_DATABASE=ims_production
DB_USERNAME=ims_user
DB_PASSWORD=your_secure_password
```

## 🔐 Database Setup

```sql
CREATE DATABASE ims_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ims_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON ims_production.* TO 'ims_user'@'localhost';
FLUSH PRIVILEGES;
```

## 🔧 Common Tasks

### View Logs
```bash
tail -f /var/www/ims/storage/logs/laravel.log
```

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Restart Services
```bash
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

### Fix Permissions
```bash
sudo chown -R www-data:www-data /var/www/ims
sudo chmod -R 755 /var/www/ims
sudo chmod -R 775 /var/www/ims/storage
sudo chmod -R 775 /var/www/ims/bootstrap/cache
```

## 📦 Files to Upload

1. **Database**: `database_backup/ims_backup_20251111_094717.sql`
2. **Environment**: Create `.env` on server from `.env.production`

## ✅ Deployment Checklist

- [ ] Server has PHP 8.1+, MySQL 8.0+, Composer, Nginx
- [ ] Repository cloned from development branch
- [ ] Dependencies installed (`composer install`)
- [ ] `.env` configured with production settings
- [ ] Database created and imported
- [ ] File permissions set (755/775)
- [ ] Application caches built
- [ ] Nginx configured for domain
- [ ] SSL certificate installed
- [ ] Application accessible via HTTPS
- [ ] All features tested

## 🆘 Troubleshooting

### 500 Error
1. Check Laravel log: `storage/logs/laravel.log`
2. Check Nginx log: `/var/log/nginx/error.log`
3. Verify `.env` database credentials
4. Ensure permissions are correct
5. Clear all caches

### Database Connection Error
1. Verify MySQL is running: `sudo systemctl status mysql`
2. Test credentials: `mysql -u ims_user -p ims_production`
3. Check `.env` DB settings
4. Verify user has privileges

### Page Not Found
1. Check Nginx configuration
2. Verify document root: `/var/www/ims/public`
3. Test Nginx config: `sudo nginx -t`
4. Restart Nginx: `sudo systemctl restart nginx`

## 📞 Support Files

- **Full Guide**: SERVER_DEPLOYMENT.md
- **Detailed Steps**: DEPLOYMENT.md
- **Checklist**: DEPLOYMENT_CHECKLIST.md
- **Ready Info**: DEPLOYMENT_READY.md

## 🎉 You're Ready!

Follow **SERVER_DEPLOYMENT.md** for step-by-step instructions to deploy to **doan.tgndigital.xyz**
