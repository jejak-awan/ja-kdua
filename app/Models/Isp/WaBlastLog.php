<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $blast_id
 * @property string $phone
 * @property string|null $name
 * @property string $status
 * @property string|null $error_message
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read WaBlast $blast
 */
class WaBlastLog extends Model
{
    protected $table = 'isp_wa_blast_logs';

    protected $fillable = [
        'blast_id',
        'phone',
        'name',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<WaBlast, $this>
     */
    public function blast(): BelongsTo
    {
        return $this->belongsTo(WaBlast::class, 'blast_id');
    }
}
