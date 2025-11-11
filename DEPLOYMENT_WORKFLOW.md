# 🚀 IMS Deployment Workflow

## Production Site Information

- **Live URL**: https://doan.tgndigital.xyz
- **Dashboard**: https://doan.tgndigital.xyz/ims-dashboard/login.php
- **API**: https://doan.tgndigital.xyz/api
- **POS**: https://doan.tgndigital.xyz/ims-dashboard/pos/login.php
- **Server**: CloudPanel (103.216.117.213:24700)
- **SSH User**: tgndigital-doan
- **Git Branch**: development

---

## 📝 Daily Deployment Workflow

### 1. Make Changes Locally

Work on your local development environment:
```bash
cd c:\xampp\htdocs\tappomarket\ims
# Make your code changes
```

### 2. Test Locally

Start local server and test:
```bash
php artisan serve
# Visit: http://127.0.0.1:8000/ims-dashboard/login.php
```

### 3. Commit and Push to GitHub

```bash
# Stage all changes
git add .

# Commit with a descriptive message
git commit -m "your descriptive message here"

# Push to development branch
git push origin development
```

### 4. Deploy to Production Server

**Connect to server via SSH:**
```bash
ssh tgndigital-doan@103.216.117.213 -p 24700
```

**Navigate to site directory:**
```bash
cd ~/htdocs/doan.tgndigital.xyz
```

**Pull latest changes:**
```bash
git pull origin development
```

**Update composer dependencies (if needed):**
```bash
composer install --optimize-autoloader --no-dev
```

**Clear and rebuild caches:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**If you added migrations:**
```bash
php artisan migrate --force
```

**Done!** Visit https://doan.tgndigital.xyz to see your changes.

---

## 🔧 Common Deployment Scenarios

### Scenario 1: Frontend Changes Only (HTML, CSS, JS)

```bash
# Local
git add .
git commit -m "Update dashboard UI"
git push origin development

# Server
cd ~/htdocs/doan.tgndigital.xyz
git pull origin development
# No cache clearing needed for static files
```

### Scenario 2: Backend Changes (Controllers, Models, Routes)

```bash
# Local
git add .
git commit -m "Add new product filter API"
git push origin development

# Server
cd ~/htdocs/doan.tgndigital.xyz
git pull origin development
php artisan config:cache
php artisan route:cache
```

### Scenario 3: Database Changes (Migrations)

```bash
# Local - Create migration
php artisan make:migration add_new_column_to_products

# Edit migration file, then
git add .
git commit -m "Add new column to products table"
git push origin development

# Server
cd ~/htdocs/doan.tgndigital.xyz
git pull origin development
php artisan migrate --force
php artisan config:cache
```

### Scenario 4: Environment Configuration Changes

```bash
# Server only - Never commit .env to Git!
cd ~/htdocs/doan.tgndigital.xyz
nano .env
# Make changes
php artisan config:clear
php artisan config:cache
```

### Scenario 5: Composer Package Updates

```bash
# Local
composer update
composer install --optimize-autoloader --no-dev
git add composer.json composer.lock
git commit -m "Update dependencies"
git push origin development

# Server
cd ~/htdocs/doan.tgndigital.xyz
git pull origin development
composer install --optimize-autoloader --no-dev
php artisan config:cache
```

---

## ⚡ Quick Deploy Script

**Save this as `deploy.sh` on your server:**

```bash
#!/bin/bash
cd ~/htdocs/doan.tgndigital.xyz
echo "🔄 Pulling latest changes..."
git pull origin development
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev --quiet
echo "🗄️  Running migrations..."
php artisan migrate --force
echo "⚡ Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "🔥 Building caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Deployment complete!"
```

**Make it executable:**
```bash
chmod +x ~/deploy.sh
```

**Then just run:**
```bash
~/deploy.sh
```

---

## 🐛 Troubleshooting

### Issue: Changes not showing up

```bash
# Clear browser cache (Ctrl+Shift+R)
# Or clear server caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Issue: 500 Error after deployment

```bash
# Check Laravel logs
tail -50 storage/logs/laravel.log

# Check permissions
chmod -R 775 storage bootstrap/cache

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Issue: Database connection error

```bash
# Verify .env database credentials
cat .env | grep DB_

# Test database connection
php artisan migrate:status
```

### Issue: Git pull conflicts

```bash
# If local changes conflict
git stash
git pull origin development
# If you need those changes back
git stash pop
```

---

## 📋 Pre-Deployment Checklist

Before pushing to production:

- [ ] Code tested locally
- [ ] No errors in browser console
- [ ] Database migrations tested
- [ ] `.env` updated on server (if needed)
- [ ] Committed with descriptive message
- [ ] Pushed to GitHub successfully

---

## 🔒 Security Reminders

- ✅ Never commit `.env` file to Git
- ✅ Always use `--no-dev` flag for composer on production
- ✅ Keep `APP_DEBUG=false` in production `.env`
- ✅ Use strong database passwords
- ✅ Keep dependencies updated monthly

---

## 📊 Monitoring

### Check Application Status

```bash
# Server uptime
uptime

# Disk space
df -h

# Recent errors
tail -50 storage/logs/laravel.log

# Database size
mysql -u doan -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'doan' GROUP BY table_schema;"
```

---

## 🆘 Emergency Rollback

If something goes wrong, rollback to previous version:

```bash
# On server
cd ~/htdocs/doan.tgndigital.xyz
git log --oneline -5  # See recent commits
git reset --hard <previous-commit-hash>
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📞 Quick Reference

| Command | Purpose |
|---------|---------|
| `git pull origin development` | Get latest code |
| `php artisan config:cache` | Cache config |
| `php artisan route:cache` | Cache routes |
| `php artisan view:cache` | Cache views |
| `php artisan migrate --force` | Run migrations |
| `composer install --optimize-autoloader --no-dev` | Install packages |
| `chmod -R 775 storage bootstrap/cache` | Fix permissions |
| `tail -50 storage/logs/laravel.log` | Check errors |

---

## 🎯 Best Practices

1. **Always test locally first**
2. **Use descriptive commit messages**
3. **Deploy during low-traffic hours**
4. **Keep backups of database**
5. **Monitor logs after deployment**
6. **Clear caches after changes**
7. **Document major changes**

---

**Happy Deploying! 🚀**

*Last Updated: November 11, 2025*
