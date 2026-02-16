<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class ServiceZone extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<ServiceZone>> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_service_zones';

    protected $fillable = [
        'name',
        'role',
        'ip_address',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
