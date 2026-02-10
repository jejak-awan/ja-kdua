<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $category
 * @property string $body
 * @property array<string, mixed>|null $variables
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class WaTemplate extends Model
{
    protected $table = 'isp_wa_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'body',
        'variables',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Render template body with variable substitution.
     *
     * @param  array<string, string>  $data
     */
    public function render(array $data): string
    {
        $body = $this->body;

        foreach ($data as $key => $value) {
            $body = str_replace('{'.$key.'}', $value, $body);
        }

        return $body;
    }
}
