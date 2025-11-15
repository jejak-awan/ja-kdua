# Deployment Guide

## Overview

This guide covers deployment of JA-CMS to production servers.

---

## Prerequisites

- Production server with SSH access
- PHP 8.2+ installed
- Composer installed
- Node.js 18+ and npm installed
- MySQL/SQLite database
- Redis (optional, for caching and queues)
- Supervisor or Systemd (for queue workers)
- Web server (Nginx/Apache)

---

## Manual Deployment

### 1. Server Setup

```bash
# Clone repository
git clone https://github.com/jejak-awan/ja-cmspro.git /var/www/ja-cms
cd /var/www/ja-cms
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit .env with production settings
nano .env
```

**Required Environment Variables:**
```env
APP_NAME="JA-CMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ja_cms
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis

CDN_ENABLED=true
CDN_URL=https://cdn.yourdomain.com
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed
```

### 6. Build Assets

```bash
npm run build
```

### 7. Create Storage Link

```bash
php artisan storage:link
```

### 8. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 9. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 10. Setup Queue Worker

**Using Supervisor:**

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ja-cms/artisan queue:work --sleep=3 --tries=3 --max-time=3600
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

**Using Systemd:**

Create `/etc/systemd/system/laravel-worker.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/ja-cms/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

---

## Automated Deployment (GitHub Actions)

### Setup GitHub Secrets

Go to your GitHub repository → Settings → Secrets and variables → Actions, and add:

1. **SSH_PRIVATE_KEY** - Your SSH private key for server access
2. **SSH_USER** - SSH username (e.g., `root`, `deploy`)
3. **SSH_HOST** - Server IP or domain (e.g., `192.168.1.1`, `server.example.com`)

### Generate SSH Key

```bash
# On your local machine
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions

# Copy public key to server
ssh-copy-id -i ~/.ssh/github_actions.pub user@server

# Copy private key content
cat ~/.ssh/github_actions
# Add this to GitHub Secrets as SSH_PRIVATE_KEY
```

### Deployment Workflow

The deployment workflow (`.github/workflows/deploy.yml`) will automatically:
1. Trigger on push to `main` branch or version tags
2. Setup SSH connection
3. Pull latest code
4. Install dependencies
5. Build assets
6. Run migrations
7. Cache configuration
8. Restart queue workers

---

## Web Server Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/ja-cms/public;
    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Increase upload size
    client_max_body_size 10M;
}
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/ja-cms/public

    <Directory /var/www/ja-cms/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

---

## Post-Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrated
- [ ] Storage link created
- [ ] Permissions set correctly
- [ ] Queue worker running
- [ ] Cache cleared and optimized
- [ ] SSL certificate installed
- [ ] CDN configured (if using)
- [ ] Backup system configured
- [ ] Monitoring setup
- [ ] Error logging configured

---

## Troubleshooting

### Queue Not Processing

```bash
# Check queue worker status
sudo supervisorctl status laravel-worker:*

# Restart queue worker
sudo supervisorctl restart laravel-worker:*

# Check logs
tail -f storage/logs/worker.log
```

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Cache Issues

```bash
# Clear all caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database Connection Issues

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## Backup Strategy

### Automated Backups

Use the built-in backup system:

```bash
# Create backup via command
php artisan backup:create

# Or use the admin interface
# Go to Backups → Create Backup
```

### Manual Backup

```bash
# Database backup
mysqldump -u user -p database_name > backup.sql

# Files backup
tar -czf files_backup.tar.gz storage/app/public
```

---

## Monitoring

### Application Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View queue logs
tail -f storage/logs/worker.log
```

### System Monitoring

- Monitor server resources (CPU, Memory, Disk)
- Monitor queue worker processes
- Monitor database connections
- Monitor cache hit rates
- Monitor API response times

---

## Security Best Practices

1. **Keep dependencies updated:**
   ```bash
   composer update
   npm update
   ```

2. **Use strong passwords** for database and admin accounts

3. **Enable HTTPS** with valid SSL certificate

4. **Configure firewall** to restrict access

5. **Regular backups** of database and files

6. **Monitor security logs** in admin dashboard

7. **Keep PHP and server software updated**

8. **Use environment variables** for sensitive data

---

## Rollback Procedure

If deployment fails:

```bash
# Revert to previous commit
git reset --hard HEAD~1

# Restore database backup
mysql -u user -p database_name < backup.sql

# Clear caches
php artisan optimize:clear

# Restart services
sudo supervisorctl restart laravel-worker:*
```

---

## References

- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [Supervisor Documentation](http://supervisord.org/)
- [GitHub Actions](https://docs.github.com/en/actions)

