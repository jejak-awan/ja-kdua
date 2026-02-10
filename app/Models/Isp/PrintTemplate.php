<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $paper_size
 * @property string $orientation
 * @property int $columns_per_row
 * @property string $html_content
 * @property string|null $css_content
 * @property array<string, string>|null $variables
 * @property bool $is_default
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PrintTemplate extends Model
{
    protected $table = 'isp_print_templates';

    protected $fillable = [
        'name',
        'type',
        'paper_size',
        'orientation',
        'columns_per_row',
        'html_content',
        'css_content',
        'variables',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_default' => 'boolean',
        'columns_per_row' => 'integer',
    ];

    /**
     * @return BelongsTo<\App\Models\Core\User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class, 'created_by');
    }

    /**
     * Available voucher template variables.
     *
     * @return array<string, string>
     */
    public static function voucherVariables(): array
    {
        return [
            '#username#' => 'Voucher username/code',
            '#password#' => 'Voucher password',
            '#profile#' => 'Service profile name',
            '#price#' => 'Voucher price (formatted)',
            '#price_raw#' => 'Voucher price (raw number)',
            '#code#' => 'Voucher code',
            '#batch_code#' => 'Batch code',
            '#expired#' => 'Expiry date',
            '#quota#' => 'Data quota',
            '#duration#' => 'Session duration',
            '#created_at#' => 'Creation date',
            '#company_name#' => 'Company name from settings',
        ];
    }

    /**
     * Available invoice template variables.
     *
     * @return array<string, string>
     */
    public static function invoiceVariables(): array
    {
        return [
            '#invoice_number#' => 'Invoice number',
            '#customer_name#' => 'Customer name',
            '#customer_address#' => 'Customer address',
            '#customer_phone#' => 'Customer phone',
            '#plan_name#' => 'Billing plan name',
            '#amount#' => 'Invoice amount (formatted)',
            '#amount_raw#' => 'Invoice amount (raw)',
            '#due_date#' => 'Due date',
            '#period#' => 'Billing period',
            '#status#' => 'Payment status',
            '#company_name#' => 'Company name from settings',
        ];
    }

    /**
     * Render template HTML by replacing hashtag variables with actual values.
     *
     * @param  array<string, string|int|float>  $data
     */
    public function render(array $data): string
    {
        $html = $this->html_content;

        foreach ($data as $key => $value) {
            $tag = '#'.$key.'#';
            $html = str_replace($tag, (string) $value, $html);
        }

        return $html;
    }

    /**
     * Get full rendered HTML with CSS wrapper.
     *
     * @param  array<string, string|int|float>  $data
     */
    public function renderFull(array $data): string
    {
        $body = $this->render($data);
        $css = $this->css_content ?? '';

        return '<style>'.$css.'</style>'.$body;
    }
}
