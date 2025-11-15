<?php

namespace App\Models;

use App\Helpers\CdnHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'size',
        'alt',
        'description',
        'folder_id',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function usages()
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function getUsageCountAttribute()
    {
        return $this->usages()->count();
    }

    public function getUrlAttribute()
    {
        if (!$this->path) {
            return null;
        }
        
        // Use CDN if enabled
        if (CdnHelper::isEnabled()) {
            return CdnHelper::mediaUrl($this->path);
        }
        
        // For public disk, use relative path to avoid localhost URL issues
        // This ensures URLs work regardless of APP_URL configuration
        if ($this->disk === 'public') {
            return '/storage/' . ltrim($this->path, '/');
        }
        
        // For other disks, use Storage URL
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbnailUrlAttribute()
    {
        if (!$this->path || !str_starts_with($this->mime_type, 'image/')) {
            return null;
        }

        // Check if thumbnail exists
        $fileName = pathinfo($this->path, PATHINFO_FILENAME);
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);
        $thumbnailPath = 'media/thumbnails/' . $fileName . '_thumb.' . $extension;

        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            // Use CDN if enabled
            if (CdnHelper::isEnabled()) {
                return CdnHelper::thumbnailUrl($thumbnailPath);
            }
            
            // For public disk, use relative path
            if ($this->disk === 'public') {
                return '/storage/' . ltrim($thumbnailPath, '/');
            }
            
            return Storage::disk($this->disk)->url($thumbnailPath);
        }

        // If no thumbnail exists, return original URL (with CDN if enabled)
        return $this->url;
    }
}
