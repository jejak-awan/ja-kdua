<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Database\Factories\Isp\ServiceProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProfile extends Model
{
    /** @use HasFactory<ServiceProfileFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_service_profiles';

    protected $fillable = [
        'name',
        'download_limit',
        'upload_limit',
        'burst_limit',
        'status',
    ];
}
