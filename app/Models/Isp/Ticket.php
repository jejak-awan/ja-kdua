<?php

declare(strict_types=1);

namespace App\Models\Isp;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
