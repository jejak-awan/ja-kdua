<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Olt extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\OltFactory> */
    use HasFactory, SoftDeletes;

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
}
