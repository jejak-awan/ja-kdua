# Deployment Guide

## Pre-Deployment Checklist

- [ ] Backup database
- [ ] Backup files (storage, uploads)
- [ ] Verify `.env` is configured for production
- [ ] Ensure Redis is running (if used)
- [ ] Ensure queue workers are configured
- [ ] Test on staging environment first

## Production Environment Setup

### 1. Environment Configuration

Update `.env` file:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Automated Deployment

Use the provided deployment script:

```bash
./deploy.sh
```

Or manually run:

```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Build assets
npm run build

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Queue Workers Setup

#### Using Supervisor

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ja-cms/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ja-cms/storage/logs/worker.log
stopwaitsecs=3600
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

#### Using Systemd

Create `/etc/systemd/system/laravel-worker.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/ja-cms/artisan queue:work redis --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

### 4. Redis Setup

```bash
# Install Redis
sudo apt-get install redis-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test Redis
redis-cli ping
```

### 5. SSL/HTTPS Setup (Let's Encrypt)

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal (already configured in certbot)
sudo certbot renew --dry-run
```

### 6. CDN Configuration

Update `config/cdn.php` with your CDN settings:

```php
return [
    'enabled' => env('CDN_ENABLED', true),
    'url' => env('CDN_URL', 'https://cdn.your-domain.com'),
    // ... other settings
];
```

### 7. Backup Schedule

Add to crontab (`crontab -e`):

```bash
# Daily database backup at 2 AM
0 2 * * * /usr/bin/mysqldump -u username -p'password' database_name > /backups/db_$(date +\%Y\%m\%d).sql

# Weekly file backup
0 3 * * 0 tar -czf /backups/files_$(date +\%Y\%m\%d).tar.gz /var/www/ja-cms/storage/app/public
```

## Post-Deployment

1. **Verify Application**
   - Check homepage loads
   - Test login functionality
   - Verify API endpoints
   - Check admin panel

2. **Monitor Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Check Queue Workers**
   ```bash
   supervisorctl status
   # or
   systemctl status laravel-worker
   ```

4. **Verify Cache**
   ```bash
   php artisan cache:clear
   php artisan config:cache
   ```

5. **Test Rate Limiting**
   - Verify 429 responses when limits exceeded
   - Check rate limit headers

## Rollback Procedure

If deployment fails:

```bash
# Restore from backup
mysql -u username -p database_name < /backups/db_backup.sql

# Restore files
tar -xzf /backups/files_backup.tar.gz -C /

# Revert code
git checkout <previous-commit-hash>
./deploy.sh
```

## Troubleshooting

### Queue Not Processing
```bash
# Check queue workers
supervisorctl status
# Restart if needed
supervisorctl restart laravel-worker:*
```

### Cache Issues
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
# Then rebuild
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Permission Issues
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Issues
- Verify `.env` database credentials
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] SSL/HTTPS enabled
- [ ] Rate limiting configured
- [ ] File permissions set correctly
- [ ] `.env` file not publicly accessible
- [ ] Regular security updates
- [ ] Firewall configured
- [ ] Backup strategy in place
