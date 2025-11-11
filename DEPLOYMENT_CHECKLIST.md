# IMS Deployment Checklist

## Pre-Deployment (Local)

- [ ] Export database using `export_database.bat`
- [ ] Test application locally one more time
- [ ] Commit all code changes to Git
- [ ] Review and update `.env.production` file
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Ensure all migrations are up to date
- [ ] Create deployment package (zip/tar.gz)

## Server Setup

- [ ] Choose hosting provider (VPS/cPanel)
- [ ] Install required software:
  - [ ] PHP 8.1+
  - [ ] MySQL 8.0+
  - [ ] Composer
  - [ ] Nginx or Apache
- [ ] Configure firewall
- [ ] Create MySQL database and user
- [ ] Setup SSH access (if VPS)

## Deployment

- [ ] Upload files to server
- [ ] Extract files to deployment directory
- [ ] Copy `.env.production` to `.env`
- [ ] Update `.env` with production settings:
  - [ ] APP_ENV=production
  - [ ] APP_DEBUG=false
  - [ ] APP_URL=https://yourdomain.com
  - [ ] Database credentials
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Generate app key: `php artisan key:generate`
- [ ] Import database SQL file
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Set file permissions (755/775)
- [ ] Configure web server (Nginx/Apache)
- [ ] Test application access

## Post-Deployment

- [ ] Setup SSL certificate (Let's Encrypt)
- [ ] Configure HTTPS redirect
- [ ] Enable OPcache for PHP
- [ ] Setup cron jobs (if needed)
- [ ] Configure automated backups
- [ ] Test all functionality:
  - [ ] Login works
  - [ ] Dashboard loads
  - [ ] Products page works
  - [ ] Orders creation works
  - [ ] API endpoints respond
  - [ ] POS system works
- [ ] Setup monitoring/logging
- [ ] Document admin credentials securely

## Security Checklist

- [ ] APP_DEBUG is false
- [ ] Strong database password
- [ ] SSL certificate installed
- [ ] File permissions are correct (not 777)
- [ ] Remove default/test users
- [ ] Configure fail2ban (optional)
- [ ] Setup firewall rules
- [ ] Regular security updates scheduled

## Performance Optimization

- [ ] Enable OPcache
- [ ] Configure Nginx gzip compression
- [ ] Database indexes in place
- [ ] Laravel caches enabled:
  - [ ] `php artisan config:cache`
  - [ ] `php artisan route:cache`
  - [ ] `php artisan view:cache`

## Backup Strategy

- [ ] Daily database backups
- [ ] Weekly full file backups
- [ ] Off-site backup storage
- [ ] Test restore procedure

## Maintenance

- [ ] Schedule regular updates
- [ ] Monitor disk space
- [ ] Monitor error logs
- [ ] Review security logs
- [ ] Update dependencies monthly

---

## Quick Commands Reference

### Deploy to server:
```bash
./deploy.sh
```

### Update application:
```bash
git pull
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Export database (Windows):
```cmd
export_database.bat
```

### Export database (Linux):
```bash
mysqldump -u username -p database_name > backup.sql
```

---

**Ready to deploy? Follow DEPLOYMENT.md for detailed instructions!**
