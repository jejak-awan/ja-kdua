<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $subtotal
 * @property string $tax
 * @property int $unique_code
 * @property string $amount
 * @property \Carbon\Carbon|null $due_date
 * @property string $status
 * @property string $billing_period
 * @property-read string $invoice_number
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Core\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItem> $items
 */
class Invoice extends Model
{
    /**
     * Get the dynamic invoice number.
     */
    public function getInvoiceNumberAttribute(): string
    {
        return 'INV/'.($this->created_at ? $this->created_at->format('Ymd') : date('Ymd')).'/'.$this->id;
    }

    protected $table = 'isp_invoices';

    protected $fillable = [
        'user_id',
        'subtotal',
        'tax',
        'unique_code',
        'amount',
        'due_date',
        'status', // unpaid, paid, cancelled
        'billing_period',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'unique_code' => 'integer',
    ];

    /**
     * Get the items for the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<InvoiceItem, $this>
     */
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    /**
     * Get the user that owns the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Core\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class, 'user_id');
    }
}
