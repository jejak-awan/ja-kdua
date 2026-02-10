<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $type
 * @property array<string, mixed>|null $details
 * @property string $status
 * @property string|null $admin_notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Customer $customer
 */
class ServiceRequest extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\ServiceRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_service_requests';

    protected $fillable = [
        'customer_id',
        'type',
        'details',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Get the customer that made the request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Customer, $this>
     */
    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
