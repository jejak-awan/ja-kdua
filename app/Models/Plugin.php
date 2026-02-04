<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'author_url',
        'plugin_url',
        'main_file',
        'settings',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function activate(): void
    {
        $this->update(['is_active' => true]);
        // Trigger plugin activation hook
        event(new \App\Events\PluginActivated($this));
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
        // Trigger plugin deactivation hook
        event(new \App\Events\PluginDeactivated($this));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function getActivePlugins(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)
            ->orderBy('priority')
            ->get();
    }
}
