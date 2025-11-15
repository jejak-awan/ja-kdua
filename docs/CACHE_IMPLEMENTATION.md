# Cache Implementation Guide

## Overview

JA-CMS implements comprehensive caching strategies using Redis (production) or file cache (development) to improve performance and reduce database load.

## Cache Configuration

### Environment Setup

**Production (.env):**
```env
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_CACHE_DB=1
```

**Development (.env):**
```env
CACHE_STORE=file
```

### Cache Helper

The `CacheHelper` class provides convenient methods for caching:

```php
use App\Helpers\CacheHelper;

// Cache content
$content = CacheHelper::rememberContent($id, CacheHelper::TTL_MEDIUM, function() use ($id) {
    return Content::findOrFail($id);
});

// Cache API response
$data = CacheHelper::rememberApiResponse('contents:list', CacheHelper::TTL_SHORT, function() {
    return Content::published()->paginate(15);
});

// Invalidate cache
CacheHelper::invalidateContent($id);
CacheHelper::invalidateAllContent();
```

## Cache Strategies

### 1. API Response Caching

Frequently accessed API endpoints are cached:

- **Content List:** 30 minutes
- **Content Detail:** 1 hour
- **Categories:** 1 hour
- **Tags:** 1 hour
- **Statistics:** 5 minutes

### 2. Database Query Caching

Expensive queries are cached:

```php
// Example: Statistics query
$stats = CacheHelper::rememberStatistics('content:stats', CacheHelper::TTL_SHORT, function() {
    return [
        'total' => Content::count(),
        'published' => Content::published()->count(),
        'draft' => Content::draft()->count(),
    ];
});
```

### 3. Tagged Cache (Redis Only)

Use cache tags for grouped invalidation:

```php
Cache::tags(['content', 'category:1'])->put('key', $value, 3600);
Cache::tags(['content'])->flush(); // Clear all content cache
```

## Cache Invalidation

### Automatic Invalidation

Cache is automatically invalidated when:

1. **Content Updated/Deleted:**
   - Content cache invalidated
   - Related category/tag cache invalidated
   - Statistics cache invalidated

2. **Media Updated/Deleted:**
   - Media cache invalidated
   - Related content cache invalidated

3. **Category Updated/Deleted:**
   - Category cache invalidated
   - Related content cache invalidated

### Manual Invalidation

```php
// Invalidate specific content
CacheHelper::invalidateContent($id);

// Invalidate all content
CacheHelper::invalidateAllContent();

// Invalidate statistics
CacheHelper::invalidateStatistics('content:stats');
```

## Implementation Examples

### ContentController

```php
public function index(Request $request)
{
    $cacheKey = 'contents_published_' . md5($request->getQueryString());
    
    return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
        // Expensive query here
        return $this->paginated($contents, 'Contents retrieved successfully');
    });
}

public function update(Request $request, Content $content)
{
    // Update content
    $content->update($validated);
    
    // Invalidate cache
    CacheHelper::invalidateContent($content->id);
    CacheHelper::invalidateAllContent();
    
    return $this->success($content, 'Content updated successfully');
}
```

### Statistics Caching

```php
public function statistics()
{
    return CacheHelper::rememberStatistics('tags:stats', CacheHelper::TTL_SHORT, function() {
        return [
            'total' => Tag::count(),
            'used' => Tag::has('contents')->count(),
            'unused' => Tag::doesntHave('contents')->count(),
        ];
    });
}
```

## Cache TTL Guidelines

- **TTL_SHORT (5 min):** Statistics, frequently changing data
- **TTL_MEDIUM (30 min):** Content lists, search results
- **TTL_LONG (1 hour):** Content details, category/tag lists
- **TTL_VERY_LONG (24 hours):** Static data, settings

## Performance Benefits

1. **Reduced Database Load:** Cached queries don't hit database
2. **Faster Response Times:** Redis/file cache is much faster
3. **Better Scalability:** Handle more concurrent requests
4. **Lower Server Costs:** Reduced database server load

## Monitoring

### Check Cache Status

```php
CacheHelper::isAvailable(); // Returns true/false
CacheHelper::getStats(); // Returns cache statistics
```

### Clear All Cache

```php
CacheHelper::clearAll(); // Use with caution!
```

Or via Artisan:
```bash
php artisan cache:clear
```

## Best Practices

1. **Cache Frequently Accessed Data:** Lists, details, statistics
2. **Use Appropriate TTL:** Balance freshness vs performance
3. **Invalidate on Updates:** Always invalidate related cache
4. **Monitor Cache Hit Rates:** Track cache effectiveness
5. **Use Tags (Redis):** For grouped invalidation
6. **Fallback Gracefully:** Handle cache failures

## Troubleshooting

### Cache Not Working

1. Check cache driver: `php artisan config:show cache`
2. Test cache: `Cache::put('test', 'value', 60); Cache::get('test');`
3. Check Redis connection (if using Redis)
4. Verify file permissions (if using file cache)

### Cache Too Aggressive

- Reduce TTL values
- Implement cache warming
- Use cache versioning

### Cache Not Invalidating

- Check invalidation calls in update/delete methods
- Verify cache tags are working (Redis only)
- Clear cache manually if needed

## Migration from File to Redis

1. Update `.env`: `CACHE_STORE=redis`
2. Configure Redis connection
3. Test cache operations
4. Monitor performance
5. Clear old file cache: `php artisan cache:clear`

## References

- [Laravel Cache Documentation](https://laravel.com/docs/cache)
- [Redis Documentation](https://redis.io/documentation)
- See `REDIS_SETUP.md` for Redis installation guide

