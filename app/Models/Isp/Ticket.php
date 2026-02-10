<?php

declare(strict_types=1);

namespace App\Models\Isp;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property string $category
 * @property string $priority
 * @property string $status
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Core\User $user
 */
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\TicketFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_tickets';

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'priority',
        'status',
        'message',
    ];

    /**
     * Get the user that opened the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
