<?php

declare(strict_types=1);

namespace App\Models\Isp;

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
class DataServer extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<DataServer>> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_data_servers';

    protected $fillable = [
        'name',
        'status',
    ];
}
