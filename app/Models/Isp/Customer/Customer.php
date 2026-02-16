<?php

declare(strict_types=1);

namespace App\Models\Isp\Customer;

use App\Models\Core\User;
use App\Models\Isp\Billing\IspPlan;
use App\Models\Isp\Billing\Transaction;
use App\Models\Isp\Customer\Contract;
use App\Models\Isp\Customer\CustomerDevice;
use App\Models\Isp\Network\Odp;
use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Customer\Partner;
use App\Models\Isp\Support\ServiceRequest;
use App\Traits\Isp\HasSaldo;
use App\Traits\Isp\HasAuditTrail;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $identity_number
 * @property string|null $identity_type
 * @property string|null $address_street
 * @property string|null $address_village
 * @property string|null $address_district
 * @property string|null $address_city
 * @property string|null $address_province
 * @property string|null $address_postal_code
 * @property string|null $coordinates
 * @property int|null $billing_plan_id
 * @property int $billing_cycle_start
 * @property \Carbon\Carbon|null $installation_date
 * @property string $status
 * @property string|null $mikrotik_login
 * @property string|null $mikrotik_password
 * @property int|null $partner_id
 * @property int|null $router_id
 * @property int|null $server_id
 * @property string|null $service_category
 * @property \Carbon\Carbon|null $billing_due_date
 * @property string|null $billing_notes
 * @property bool $is_taxed
 * @property string|null $unique_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $olt_id
 * @property int|null $odp_id
 * @property int|null $odp_port
 * @property float $saldo
 * @property string|null $telegram_id
 * @property string|null $onu_serial
 * @property string|null $onu_model
 * @property string|null $olt_port
 * @property string|null $olt_onu_index
 * @property array<string, mixed>|null $metadata
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float|null $total_revenue
 * @property string|null $onu_sn
 * @property string|null $ip_address
 * @property string|null $nas_ip
 * @property string|null $onu_index
 * @property mixed $risk
 * @property mixed $intensity
 * @property-read \App\Models\Core\User $user
 * @property-read \App\Models\Isp\Billing\IspPlan|null $plan
 * @property-read \App\Models\Isp\Network\ServiceNode|null $olt
 * @property-read \App\Models\Isp\Network\Odp|null $odp
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\Billing\Transaction> $transactions
 */
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\CustomerFactory> */
    use HasFactory, HasSaldo, HasAuditTrail, LogsActivity, SoftDeletes;

    protected $table = 'isp_customers';

    protected $fillable = [
        'user_id',
        'saldo',
        'identity_number',
        'identity_type',
        'address_street',
        'address_village',
        'address_district',
        'address_city',
        'address_province',
        'address_postal_code',
        'coordinates',
        'billing_plan_id',
        'billing_cycle_start',
        'installation_date',
        'status',
        'mikrotik_login',
        'mikrotik_password',
        'telegram_id',
        'onu_serial',
        'onu_model',
        'partner_id',
        'router_id',
        'server_id',
        'service_category',
        'billing_due_date',
        'billing_notes',
        'is_taxed',
        'unique_code',
        'latitude',
        'longitude',
        'olt_id',
        'olt_port',
        'olt_onu_index',
        'odp_id',
        'odp_port',
        'current_usage_bytes',
        'last_usage_reset_at',
        'is_fup_active',
        'metadata',
    ];

    protected $casts = [
        'billing_cycle_start' => 'integer',
        'installation_date' => 'date',
        'billing_due_date' => 'date',
        'is_taxed' => 'boolean',
        'saldo' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'odp_id' => 'integer',
        'odp_port' => 'integer',
        'current_usage_bytes' => 'integer',
        'last_usage_reset_at' => 'datetime',
        'is_fup_active' => 'boolean',
        'mikrotik_password' => 'encrypted',
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<IspPlan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(IspPlan::class, 'billing_plan_id');
    }

    /**
     * @return BelongsTo<ServiceNode, $this>
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'olt_id');
    }

    /**
     * @return BelongsTo<ServiceNode, $this>
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'router_id');
    }

    /**
     * @return BelongsTo<Odp, $this>
     */
    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }

    /**
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    /**
     * @return HasMany<CustomerDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class, 'customer_id');
    }

    /**
     * @return HasMany<Contract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'customer_id');
    }

    /**
     * @return HasMany<ServiceRequest, $this>
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    /**
     * @return MorphMany<Transaction, $this>
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'parent');
    }
}
