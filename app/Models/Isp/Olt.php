<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $ip_address
 * @property string|null $type
 * @property string|null $username
 * @property string|null $password
 * @property int|null $port
 * @property string $status
 * @property array<string, mixed>|null $details
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\Customer> $customers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\Odp> $odps
 */
class Olt extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\OltFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'olts';

    protected $fillable = [
        'name',
        'ip_address',
        'type',
        'username',
        'password',
        'port',
        'status',
        'details',
    ];

    protected $casts = [
        'port' => 'integer',
        'details' => 'array',
    ];

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'olt_id');
    }

    /**
     * @return HasMany<Odp, $this>
     */
    public function odps(): HasMany
    {
        return $this->hasMany(Odp::class, 'olt_id');
    }
}
