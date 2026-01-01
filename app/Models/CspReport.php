<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CspReport extends Model
{
    protected $fillable = [
        'document_uri',
        'violated_directive',
        'blocked_uri',
        'source_file',
        'line_number',
        'user_agent',
        'ip_address',
        'raw_report',
        'status',
    ];

    protected $casts = [
        'raw_report' => 'array',
        'line_number' => 'integer',
    ];

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeByDirective($query, $directive)
    {
        return $query->where('violated_directive', $directive);
    }
}
