# CDN Setup Guide

## Overview

JA-CMS supports CDN (Content Delivery Network) for serving media files, improving performance by delivering content from edge locations closer to users.

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# CDN Configuration
CDN_ENABLED=true
CDN_URL=https://cdn.example.com
CDN_PATH_PREFIX=/storage

# Image Optimization
CDN_IMAGE_QUALITY=85
CDN_IMAGE_MAX_WIDTH=1920
CDN_IMAGE_MAX_HEIGHT=1080
CDN_IMAGE_FORMAT=auto
```

### CDN Providers

JA-CMS works with any CDN provider that supports:
- Static file hosting
- Custom domain configuration
- Optional image optimization

**Popular CDN Providers:**
- Cloudflare
- AWS CloudFront
- Google Cloud CDN
- KeyCDN
- BunnyCDN
- Fastly

## Setup Instructions

### 1. Cloudflare

1. Create a Cloudflare account
2. Add your domain
3. Configure DNS
4. Enable CDN
5. Set up custom domain for media: `cdn.yourdomain.com`
6. Configure origin: Point to your main domain
7. Set `CDN_URL=https://cdn.yourdomain.com` in `.env`

### 2. AWS CloudFront

1. Create S3 bucket for media files
2. Create CloudFront distribution
3. Configure origin: S3 bucket or your server
4. Set up custom domain (optional)
5. Configure cache behaviors
6. Set `CDN_URL=https://your-distribution.cloudfront.net` in `.env`

### 3. Generic CDN

1. Upload media files to CDN storage
2. Configure CDN to serve from storage
3. Set up custom domain (optional)
4. Set `CDN_URL` in `.env` to your CDN URL

## Usage

### Automatic CDN URLs

Once configured, media URLs automatically use CDN:

```php
$media = Media::find(1);
echo $media->url; // Returns CDN URL if enabled
echo $media->thumbnail_url; // Returns CDN URL for thumbnail
```

### Manual CDN URL Generation

```php
use App\Helpers\CdnHelper;

// Generate CDN URL
$url = CdnHelper::url('media/image.jpg');

// Generate optimized image URL
$url = CdnHelper::optimizedImageUrl('media/image.jpg', 800, 600, 85);
```

### Check CDN Status

```php
if (CdnHelper::isEnabled()) {
    // CDN is enabled
}
```

## Image Optimization

CDN can optimize images on-the-fly:

```php
// Get optimized image with specific dimensions
$url = CdnHelper::optimizedImageUrl('media/image.jpg', 800, 600);

// This generates: https://cdn.example.com/storage/media/image.jpg?w=800&h=600&q=85
```

**Supported Parameters:**
- `w` - Width
- `h` - Height
- `q` - Quality (1-100)
- `f` - Format (webp, jpg, png)

## Cache Control

CDN cache control headers are configured per file type:

- **Images:** 1 year (31536000 seconds)
- **Documents:** 30 days (2592000 seconds)
- **Default:** 1 day (86400 seconds)

## Migration

### Upload Existing Media to CDN

1. Set up CDN storage
2. Upload existing media files to CDN
3. Enable CDN in `.env`
4. Test media URLs

### Automated Upload

You can create a command to sync media to CDN:

```php
php artisan make:command SyncMediaToCdn
```

## Best Practices

1. **Use CDN for Production:** Enable CDN only in production
2. **Optimize Images:** Use image optimization features
3. **Cache Control:** Configure appropriate cache headers
4. **Monitor Performance:** Track CDN performance metrics
5. **Fallback:** Ensure fallback to local storage if CDN fails

## Troubleshooting

### Media Not Loading from CDN

1. Check `CDN_ENABLED=true` in `.env`
2. Verify `CDN_URL` is correct
3. Check CDN configuration
4. Verify files are uploaded to CDN
5. Check CDN logs

### Images Not Optimizing

1. Verify CDN supports image optimization
2. Check image optimization parameters
3. Review CDN documentation for supported formats

### Cache Issues

1. Clear CDN cache
2. Check cache control headers
3. Verify cache invalidation is working

## Performance Benefits

1. **Faster Load Times:** Content served from edge locations
2. **Reduced Server Load:** Offload media serving to CDN
3. **Better Global Performance:** Lower latency worldwide
4. **Bandwidth Savings:** Reduced bandwidth usage on main server
5. **Image Optimization:** Automatic image optimization

## Security

1. **HTTPS:** Always use HTTPS for CDN
2. **Access Control:** Configure CDN access controls
3. **Hotlink Protection:** Enable hotlink protection
4. **Signed URLs:** Use signed URLs for private content (if supported)

## References

- [Cloudflare Documentation](https://developers.cloudflare.com/)
- [AWS CloudFront Documentation](https://docs.aws.amazon.com/cloudfront/)
- [Laravel Filesystem Documentation](https://laravel.com/docs/filesystem)

