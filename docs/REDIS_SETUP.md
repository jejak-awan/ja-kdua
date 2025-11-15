# Redis Setup Guide

## Overview

JA-CMS uses Redis for caching and queue management to improve performance. This guide explains how to set up and configure Redis for the application.

## Prerequisites

- Redis server installed and running
- PHP Redis extension (phpredis) or Predis package installed

## Installation

### 1. Install Redis Server

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

**CentOS/RHEL:**
```bash
sudo yum install redis
sudo systemctl start redis
sudo systemctl enable redis
```

**macOS:**
```bash
brew install redis
brew services start redis
```

### 2. Install PHP Redis Extension

**Ubuntu/Debian:**
```bash
sudo apt-get install php-redis
sudo systemctl restart php-fpm  # or your PHP service
```

**Or install via PECL:**
```bash
sudo pecl install redis
```

**macOS:**
```bash
brew install php-redis
```

### 3. Install Predis Package (Alternative/Backup)

If phpredis extension is not available, Laravel will use Predis:

```bash
composer require predis/predis
```

## Configuration

### 1. Environment Variables

Add the following to your `.env` file:

```env
# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=ja-cms-cache-

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Redis Cache Database (separate from default)
REDIS_CACHE_DB=1

# Redis Queue Database (if using Redis for queues)
REDIS_QUEUE_DB=2
```

### 2. Verify Redis Connection

Test Redis connection:

```bash
redis-cli ping
```

Should return: `PONG`

### 3. Test from Laravel

```bash
php artisan tinker
```

```php
Cache::store('redis')->put('test', 'value', 60);
Cache::store('redis')->get('test');
```

## Usage

### Cache Configuration

Laravel automatically uses Redis when `CACHE_STORE=redis` is set in `.env`.

### Cache Operations

```php
// Store in cache
Cache::put('key', 'value', 3600); // 1 hour
Cache::remember('key', 3600, function() {
    return expensiveOperation();
});

// Retrieve from cache
$value = Cache::get('key');
$value = Cache::get('key', 'default');

// Check if exists
if (Cache::has('key')) {
    // ...
}

// Delete from cache
Cache::forget('key');
Cache::flush(); // Clear all cache
```

### Cache Tags (Redis Only)

Redis supports cache tags for grouped invalidation:

```php
Cache::tags(['users', 'posts'])->put('user:1:posts', $posts, 3600);
Cache::tags(['users'])->flush(); // Clear all user-related cache
```

## Performance Benefits

1. **Faster Response Times:** Redis is in-memory, much faster than file/database cache
2. **Scalability:** Redis can handle high concurrent requests
3. **Persistence:** Redis can persist data to disk
4. **Advanced Features:** Tags, pub/sub, transactions

## Monitoring

### Check Redis Status

```bash
redis-cli info stats
redis-cli info memory
```

### Monitor Redis Commands

```bash
redis-cli monitor
```

### Check Cache Keys

```bash
redis-cli
> KEYS ja-cms-cache-*
> GET ja-cms-cache-key-name
```

## Troubleshooting

### Connection Refused

- Check if Redis is running: `sudo systemctl status redis`
- Check Redis port: `netstat -tulpn | grep 6379`
- Check firewall settings

### Authentication Required

If Redis requires password, set in `.env`:
```env
REDIS_PASSWORD=your_password
```

### Memory Issues

Monitor Redis memory usage:
```bash
redis-cli info memory
```

Configure max memory in `/etc/redis/redis.conf`:
```
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## Production Recommendations

1. **Use Redis Persistence:**
   - Enable RDB snapshots or AOF (Append Only File)
   - Configure in `/etc/redis/redis.conf`

2. **Set Memory Limits:**
   - Configure `maxmemory` based on available RAM
   - Use `allkeys-lru` eviction policy

3. **Security:**
   - Set Redis password in production
   - Bind to localhost or use firewall
   - Disable dangerous commands

4. **Monitoring:**
   - Set up Redis monitoring (RedisInsight, Grafana)
   - Monitor memory usage and hit rates

5. **Backup:**
   - Regular RDB snapshots
   - AOF for better durability

## Cache Invalidation Strategy

### Tagged Cache

Use cache tags for related data:

```php
// Store with tags
Cache::tags(['content', 'category:1'])->put('content:1', $content, 3600);

// Invalidate all content cache
Cache::tags(['content'])->flush();

// Invalidate specific category
Cache::tags(['category:1'])->flush();
```

### Manual Invalidation

```php
// On content update
Cache::forget('content:' . $id);
Cache::tags(['content'])->flush();
```

## Queue Configuration (Future)

Redis can also be used for Laravel queues:

```env
QUEUE_CONNECTION=redis
```

This will be configured in Phase 2 Queue implementation.

## References

- [Laravel Cache Documentation](https://laravel.com/docs/cache)
- [Redis Documentation](https://redis.io/documentation)
- [Predis Documentation](https://github.com/predis/predis)

