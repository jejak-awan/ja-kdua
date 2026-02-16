<?php

declare(strict_types=1);

namespace App\Models\Isp\Operations;

use App\Models\Core\User;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Support\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $technician_id
 * @property int $customer_id
 * @property int $service_request_id
 * @property string $type
 * @property string $status
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $completed_at
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read User $technician
 * @property-read Customer $customer
 * @property-read ServiceRequest $serviceRequest
 * @property-read float $distance_km
 */
class TechnicianDeployment extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\Operations\TechnicianDeploymentFactory> */
    use HasFactory;

    protected $table = 'isp_technician_deployments';

    protected $fillable = [
        'technician_id',
        'customer_id',
        'service_request_id',
        'type',
        'status',
        'scheduled_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @return BelongsTo<ServiceRequest, $this>
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }
}
