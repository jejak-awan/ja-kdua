<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $contract_number
 * @property string $title
 * @property string|null $description
 * @property string|null $file_path
 * @property string $start_date
 * @property string|null $end_date
 * @property string $status
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Contract extends Model
{
    use SoftDeletes;

    protected $table = 'isp_contracts';

    protected $fillable = [
        'customer_id',
        'contract_number',
        'title',
        'description',
        'file_path',
        'start_date',
        'end_date',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
