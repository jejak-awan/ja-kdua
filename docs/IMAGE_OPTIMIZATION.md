# Image Optimization Guide

## Overview

JA-CMS implements comprehensive image optimization to improve performance, reduce bandwidth usage, and enhance user experience.

## Features

### 1. Automatic Image Optimization

Images are automatically optimized during upload:

- **Resize:** Large images are resized to max 1920px width
- **Quality:** Images are compressed to 85% quality (configurable)
- **Format:** Support for WebP conversion (if enabled)

### 2. Thumbnail Generation

Thumbnails are automatically generated for images:

- **Size:** 300x300px by default (configurable)
- **Format:** Same format as original
- **Quality:** 85% (configurable)
- **Storage:** Stored in `media/thumbnails/` directory

### 3. Lazy Loading

Images are loaded lazily using Intersection Observer API:

- **Performance:** Images load only when visible in viewport
- **Bandwidth:** Reduces initial page load time
- **User Experience:** Faster page rendering

### 4. CDN Integration

CDN can optimize images on-the-fly:

- **Dynamic Resizing:** Resize images via URL parameters
- **Format Conversion:** Convert to WebP/AVIF automatically
- **Quality Control:** Adjust quality via URL parameters

## Configuration

### Environment Variables

```env
# Image Optimization
CDN_IMAGE_QUALITY=85
CDN_IMAGE_MAX_WIDTH=1920
CDN_IMAGE_MAX_HEIGHT=1080
CDN_IMAGE_FORMAT=auto
```

### Thumbnail Settings

Thumbnails are generated with these defaults:
- Width: 300px
- Height: 300px
- Quality: 85%

## Usage

### Automatic Optimization

Images are automatically optimized during upload:

```php
// In MediaController::upload()
// Images are automatically optimized if optimize=true (default)
```

### Manual Thumbnail Generation

```php
use App\Jobs\ProcessImageJob;

// Generate thumbnail via queue
ProcessImageJob::dispatch($mediaId, 'thumbnail', 300, 300, 85);
```

### Manual Image Resize

```php
use App\Jobs\ProcessImageJob;

// Resize image via queue
ProcessImageJob::dispatch($mediaId, 'resize', 800, 600, 85);
```

### Manual Image Optimization

```php
use App\Jobs\ProcessImageJob;

// Optimize image via queue
ProcessImageJob::dispatch($mediaId, 'optimize', null, null, 85);
```

## Frontend Lazy Loading

### Using LazyImage Component

```vue
<template>
    <LazyImage
        :src="imageUrl"
        :alt="imageAlt"
        image-class="w-full h-auto"
        root-margin="50px"
    />
</template>

<script setup>
import LazyImage from '@/components/LazyImage.vue';
</script>
```

### LazyImage Props

- `src` - Image source URL (required)
- `alt` - Alt text (optional)
- `placeholder` - Placeholder image URL (optional)
- `imageClass` - CSS classes for image (optional)
- `loadingClass` - CSS classes when loading (optional)
- `loadedClass` - CSS classes when loaded (optional)
- `rootMargin` - Intersection Observer root margin (default: '50px')

## CDN Image Optimization

### URL Parameters

CDN can optimize images via URL parameters:

```
https://cdn.example.com/storage/media/image.jpg?w=800&h=600&q=85&f=webp
```

**Parameters:**
- `w` - Width
- `h` - Height
- `q` - Quality (1-100)
- `f` - Format (webp, jpg, png, avif)

### Using CdnHelper

```php
use App\Helpers\CdnHelper;

// Generate optimized image URL
$url = CdnHelper::optimizedImageUrl('media/image.jpg', 800, 600, 85);
// Returns: https://cdn.example.com/storage/media/image.jpg?w=800&h=600&q=85
```

## Best Practices

### 1. Image Sizes

- **Thumbnails:** 300x300px for grid views
- **Medium:** 800x600px for content images
- **Large:** 1920x1080px maximum for full-width images

### 2. Quality Settings

- **Thumbnails:** 75-85% quality
- **Content Images:** 85-90% quality
- **Hero Images:** 90-95% quality

### 3. Format Selection

- **WebP:** Best compression, modern browsers
- **JPEG:** Universal support, good for photos
- **PNG:** Lossless, good for graphics/logos

### 4. Lazy Loading

- Use lazy loading for images below the fold
- Set appropriate `rootMargin` for smooth loading
- Provide placeholder images for better UX

## Performance Benefits

1. **Reduced File Sizes:** 30-70% smaller file sizes
2. **Faster Load Times:** Smaller images load faster
3. **Bandwidth Savings:** Reduced bandwidth usage
4. **Better SEO:** Faster pages rank better
5. **Improved UX:** Faster page rendering

## Queue Processing

Image processing is handled asynchronously via queue:

```php
// Process in background
ProcessImageJob::dispatch($mediaId, 'thumbnail');
```

**Benefits:**
- Non-blocking uploads
- Better user experience
- Scalable processing
- Retry on failure

## Monitoring

### Check Image Sizes

```bash
# Check storage directory
du -sh storage/app/public/media/

# Check thumbnail directory
du -sh storage/app/public/media/thumbnails/
```

### Check Queue Jobs

```bash
# Check pending image processing jobs
php artisan queue:work --queue=default
```

## Troubleshooting

### Images Not Optimizing

1. Check if GD or Imagick extension is installed
2. Verify image processing library is available
3. Check queue worker is running
4. Review logs for errors

### Thumbnails Not Generating

1. Check storage permissions
2. Verify thumbnail directory exists
3. Check queue jobs for failures
4. Review ProcessImageJob logs

### Lazy Loading Not Working

1. Check browser support for IntersectionObserver
2. Verify LazyImage component is imported
3. Check console for errors
4. Verify image URLs are correct

## References

- [Intervention Image Documentation](https://image.intervention.io/)
- [Intersection Observer API](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)
- [WebP Format](https://developers.google.com/speed/webp)

