<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $olt_id
 * @property int|null $user_id
 * @property string $command
 * @property string|null $response
 * @property bool $is_success
 * @property int|null $execution_time_ms
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ServiceNode $olt
 * @property-read User|null $user
 */
class OltCommandLog extends Model
{
    protected $table = 'isp_olt_command_logs';

    protected $fillable = [
        'olt_id',
        'user_id',
        'command',
        'response',
        'is_success',
        'execution_time_ms',
    ];

    protected $casts = [
        'is_success' => 'boolean',
        'execution_time_ms' => 'integer',
        'olt_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * @return BelongsTo<ServiceNode, $this>
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(ServiceNode::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
