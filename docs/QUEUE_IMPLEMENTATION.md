# Queue Implementation Guide

## Overview

JA-CMS implements Laravel Queue system for asynchronous processing of time-consuming tasks, improving application performance and user experience.

## Queue Configuration

### Environment Setup

**Production (.env):**
```env
QUEUE_CONNECTION=redis
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90
```

**Development (.env):**
```env
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90
```

**Synchronous (Testing):**
```env
QUEUE_CONNECTION=sync
```

## Available Queue Jobs

### 1. ProcessImageJob

Processes images asynchronously (thumbnail generation, resize, optimization).

**Usage:**
```php
use App\Jobs\ProcessImageJob;

// Generate thumbnail
ProcessImageJob::dispatch($mediaId, 'thumbnail', 300, 300, 85);

// Resize image
ProcessImageJob::dispatch($mediaId, 'resize', 800, 600, 85);

// Optimize image
ProcessImageJob::dispatch($mediaId, 'optimize', null, null, 85);
```

**Parameters:**
- `$mediaId` - Media ID
- `$action` - Action type: 'thumbnail', 'resize', 'optimize'
- `$width` - Width (optional)
- `$height` - Height (optional)
- `$quality` - Image quality (default: 85)

**Retry:** 3 attempts with backoff [60s, 120s, 300s]
**Timeout:** 5 minutes

### 2. SendEmailJob

Sends emails asynchronously with template support.

**Usage:**
```php
use App\Jobs\SendEmailJob;

// Simple email
SendEmailJob::dispatch(
    'user@example.com',
    'Welcome!',
    'Welcome to our platform...'
);

// With email template
SendEmailJob::dispatch(
    'user@example.com',
    'Welcome!',
    '',
    'welcome-email',
    ['name' => 'John Doe', 'site_name' => 'My Site']
);
```

**Parameters:**
- `$to` - Recipient email
- `$subject` - Email subject
- `$body` - Email body (ignored if template used)
- `$templateSlug` - Email template slug (optional)
- `$data` - Template data (optional)
- `$from` - Sender email (optional)
- `$fromName` - Sender name (optional)

**Retry:** 3 attempts with backoff [30s, 60s, 120s]
**Timeout:** 1 minute

### 3. CreateBackupJob

Creates database/files/full backups asynchronously.

**Usage:**
```php
use App\Jobs\CreateBackupJob;

// Database backup
CreateBackupJob::dispatch('backup_name', 'Backup description', 'database');

// Files backup
CreateBackupJob::dispatch('backup_name', 'Backup description', 'files');

// Full backup
CreateBackupJob::dispatch('backup_name', 'Backup description', 'full');
```

**Parameters:**
- `$name` - Backup name (optional, auto-generated if null)
- `$description` - Backup description (optional)
- `$type` - Backup type: 'database', 'files', 'full'

**Retry:** 1 attempt (backups should only run once)
**Timeout:** 30 minutes

### 4. IndexSearchJob

Indexes content for search functionality.

**Usage:**
```php
use App\Jobs\IndexSearchJob;

// Index single content
IndexSearchJob::dispatch($contentId);

// Reindex all content
IndexSearchJob::dispatch(null, true);
```

**Parameters:**
- `$contentId` - Content ID to index (optional)
- `$reindexAll` - Reindex all content (default: false)

**Retry:** 3 attempts with backoff [60s, 120s, 300s]
**Timeout:** 5 minutes

## Running Queue Workers

### Development

```bash
php artisan queue:work
```

### Production (with Supervisor)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program : laravel-worker]
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

### Using Systemd

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
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

## Failed Jobs

### View Failed Jobs

```bash
php artisan queue:failed
```

### Retry Failed Job

```bash
php artisan queue:retry {job-id}
php artisan queue:retry all
```

### Delete Failed Job

```bash
php artisan queue:forget {job-id}
php artisan queue:flush
```

### Failed Job Table

Failed jobs are stored in `failed_jobs` table. Create migration:

```bash
php artisan queue:failed-table
php artisan migrate
```

## Monitoring

### Queue Status

```bash
php artisan queue:monitor redis:default --max=1000
```

### Check Queue Size

```bash
# Redis
redis-cli LLEN queues:default

# Database
php artisan tinker
>>> DB::table('jobs')->count();
```

### Queue Statistics

```php
use Illuminate\Support\Facades\Queue;

// Get queue size
$size = Queue::size();

// Check if queue is empty
$isEmpty = Queue::size() === 0;
```

## Best Practices

1. **Use Queues for Heavy Tasks:**
   - Image processing
   - Email sending
   - Backup creation
   - Search indexing
   - Report generation

2. **Set Appropriate Timeouts:**
   - Image processing: 5 minutes
   - Email sending: 1 minute
   - Backup creation: 30 minutes
   - Search indexing: 5 minutes

3. **Implement Retry Logic:**
   - Use exponential backoff
   - Set maximum retry attempts
   - Log failures for debugging

4. **Monitor Queue Health:**
   - Set up queue monitoring
   - Alert on failed jobs
   - Track queue size

5. **Handle Failures Gracefully:**
   - Implement `failed()` method
   - Log errors
   - Notify administrators

## Integration Examples

### MediaController

```php
// Instead of processing immediately
ProcessImageJob::dispatch($media->id, 'thumbnail');

// Or with delay
ProcessImageJob::dispatch($media->id, 'thumbnail')
    ->delay(now()->addMinutes(1));
```

### AuthController

```php
// Send verification email via queue
SendEmailJob::dispatch(
    $user->email,
    'Verify Your Email',
    '',
    'email-verification',
    ['user' => $user, 'verification_url' => $url]
);
```

### BackupController

```php
// Create backup via queue
CreateBackupJob::dispatch(
    $request->name,
    $request->description,
    'database'
);
```

### ContentController

```php
// Index content after publish
if ($content->status === 'published') {
    IndexSearchJob::dispatch($content->id);
}
```

## Troubleshooting

### Jobs Not Processing

1. Check queue worker is running: `ps aux | grep queue:work`
2. Check queue connection: `php artisan config:show queue`
3. Check logs: `tail -f storage/logs/laravel.log`

### Jobs Failing

1. Check failed jobs: `php artisan queue:failed`
2. Check job logs in `storage/logs/`
3. Review exception messages

### High Queue Size

1. Increase number of workers
2. Optimize job processing time
3. Use priority queues for important jobs

## Performance Tips

1. **Use Multiple Workers:** Run multiple queue workers for parallel processing
2. **Priority Queues:** Use priority queues for important jobs
3. **Batch Jobs:** Group related jobs using job batching
4. **Rate Limiting:** Implement rate limiting for external API calls
5. **Cache Results:** Cache expensive operations results

## References

- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Supervisor Documentation](http://supervisord.org/)
- [Redis Queue](https://redis.io/docs/manual/patterns/queue/)

