<?php

namespace App\Models;

use App\Helpers\CdnHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @property string|null $thumbnail_path
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MediaUsage[] $usages
 * @property-read array $tag_names
 * @property-read string|null $url
 * @property-read string|null $thumbnail_url
 * @property-read int $usage_count
 */
class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'thumbnail_path',
        'size',
        'alt',
        'description',
        'caption',
        'folder_id',
        'author_id',
        'is_shared',
    ];

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected $casts = [
        'size' => 'integer',
        'is_shared' => 'boolean',
    ];

    protected $appends = ['url', 'thumbnail_url', 'tag_names'];

    public function folder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function usages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'media_tag');
    }

    public function getTagNamesAttribute()
    {
        return $this->tags->pluck('name')->toArray();
    }

    public function getUsageCountAttribute()
    {
        return $this->usages()->count();
    }

    public function getUrlAttribute()
    {
        if (! $this->path) {
            return null;
        }

        // Use CDN if enabled
        if (CdnHelper::isEnabled()) {
            return CdnHelper::mediaUrl($this->path);
        }

        // For public disk, use relative path to avoid localhost URL issues
        // This ensures URLs work regardless of APP_URL configuration
        if ($this->disk === 'public') {
            return '/storage/'.ltrim($this->path, '/');
        }

        // For other disks, use Storage URL
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbnailUrlAttribute()
    {
        if (! $this->path || ! str_starts_with($this->mime_type, 'image/')) {
            return null;
        }

        // Check if thumbnail exists
        $fileName = pathinfo($this->path, PATHINFO_FILENAME);
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);

        // For SVG files, thumbnail is saved as PNG
        $isSvg = $this->mime_type === 'image/svg+xml' || str_ends_with($this->path, '.svg');
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailPath = 'media/thumbnails/'.$fileName.'_thumb.'.$thumbnailExtension;

        // Check if thumbnail file actually exists
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            // Use CDN if enabled
            if (CdnHelper::isEnabled()) {
                return CdnHelper::thumbnailUrl($thumbnailPath);
            }

            // For public disk, use relative path
            if ($this->disk === 'public') {
                return '/storage/'.ltrim($thumbnailPath, '/');
            }

            return Storage::disk($this->disk)->url($thumbnailPath);
        }

        // If no thumbnail exists, return original URL (with CDN if enabled)
        return $this->url;
    }
}
