<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'author_url',
        'preview_image',
        'settings',
        'custom_css',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getActiveTheme()
    {
        return self::where('is_active', true)->first();
    }

    public function activate()
    {
        // Deactivate all other themes
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this theme
        $this->update(['is_active' => true]);
    }
}
