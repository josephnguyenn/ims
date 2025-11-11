# 🚀 Production Deployment Checklist

## Environment Setup
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate new `APP_KEY` for production
- [ ] Configure proper database credentials
- [ ] Set up SSL/HTTPS

## Performance Optimizations
- [ ] Enable PHP OPcache
- [ ] Configure MySQL query cache
- [ ] Set up Redis for session/cache storage
- [ ] Enable gzip compression
- [ ] Configure CDN for static assets

## Security
- [ ] Remove test users (`admin@test.com`)
- [ ] Set strong passwords for all users
- [ ] Configure firewall rules
- [ ] Set up regular database backups
- [ ] Enable Laravel's rate limiting
- [ ] Configure CORS for specific domains only

## Database Maintenance
- [ ] Set up automated backups
- [ ] Monitor database performance
- [ ] Plan for data archiving (old orders/logs)
- [ ] Set up database monitoring alerts

## Recommended Server Specs
- **Minimum**: 2GB RAM, 2 CPU cores, 20GB SSD
- **Recommended**: 4GB RAM, 4 CPU cores, 50GB SSD
- **Database**: Separate MySQL server if high traffic

## Monitoring & Maintenance
- [ ] Set up application logging
- [ ] Monitor disk space usage
- [ ] Regular security updates
- [ ] Performance monitoring dashboard
- [ ] Error tracking (e.g., Sentry)

## Backup Strategy
- **Database**: Daily automated backups, keep 30 days
- **Files**: Weekly backup of application files
- **Test Recovery**: Monthly backup restoration tests