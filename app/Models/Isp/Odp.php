<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $olt_id
 * @property int $total_ports
 * @property string|null $location_address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $status
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Isp\Olt $olt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\Customer> $customers
 * @property-read int $available_ports
 */
class Odp extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\OdpFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'olt_id',
        'total_ports',
        'location_address',
        'latitude',
        'longitude',
        'status',
        'description',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'total_ports' => 'integer',
    ];

    /**
     * @return BelongsTo<Olt, $this>
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'odp_id');
    }

    public function getAvailablePortsAttribute(): int
    {
        return $this->total_ports - $this->customers()->count();
    }
}
