<?php

declare(strict_types=1);

namespace App\Models\Isp\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $driver
 * @property array<string, mixed> $config
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PaymentGateway extends Model
{
    protected $table = 'isp_payment_gateways';

    protected $fillable = [
        'name',
        'slug',
        'driver',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];
}
