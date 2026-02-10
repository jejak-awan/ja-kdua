<?php

namespace App\Events;

use App\Models\Core\Plugin;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PluginActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Plugin $plugin)
    {
        //
    }
}
