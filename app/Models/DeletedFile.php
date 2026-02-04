<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $original_path
 * @property string $trash_path
 * @property string $disk
 * @property string $name
 * @property string $type
 * @property int $size
 * @property string $extension
 * @property string $mime_type
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $deletedByUser
 * @property-read string $formatted_size
 */
class DeletedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_path',
        'trash_path',
        'disk',
        'name',
        'type',
        'size',
        'extension',
        'mime_type',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'size' => 'integer',
    ];

    /**
     * Get the user who deleted this file
     */
    public function deletedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope for files only
     */
    public function scopeFiles($query)
    {
        return $query->where('type', 'file');
    }

    /**
     * Scope for folders only
     */
    public function scopeFolders($query)
    {
        return $query->where('type', 'folder');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute()
    {
        if (! $this->size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($this->size, 1024));
        $val = round($this->size / pow(1024, $i), 2);

        return $val.' '.$units[$i];
    }
}
