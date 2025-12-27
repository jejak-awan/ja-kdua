<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_path',
        'trash_path',
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
    public function deletedByUser()
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
        if (!$this->size) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($this->size, 1024));
        return round($this->size / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
