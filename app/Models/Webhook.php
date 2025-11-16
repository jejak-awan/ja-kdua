<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'event',
        'method',
        'headers',
        'payload_template',
        'is_active',
        'timeout',
        'retry_count',
        'max_retries',
        'last_triggered_at',
        'success_count',
        'failure_count',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload_template' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function trigger($data)
    {
        if (! $this->is_active) {
            return false;
        }

        try {
            $payload = $this->buildPayload($data);

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers ?? [])
                ->{strtolower($this->method)}($this->url, $payload);

            if ($response->successful()) {
                $this->increment('success_count');
                $this->update(['last_triggered_at' => now()]);

                return true;
            } else {
                $this->increment('failure_count');

                // Retry if not exceeded max retries
                if ($this->retry_count < $this->max_retries) {
                    $this->increment('retry_count');
                    // Could implement retry queue here
                }

                \Log::error('Webhook failed with status: '.$response->status(), [
                    'webhook_id' => $this->id,
                    'url' => $this->url,
                ]);

                return false;
            }
        } catch (\Exception $e) {
            $this->increment('failure_count');

            // Retry if not exceeded max retries
            if ($this->retry_count < $this->max_retries) {
                $this->increment('retry_count');
                // Could implement retry queue here
            }

            \Log::error('Webhook failed: '.$e->getMessage(), [
                'webhook_id' => $this->id,
                'url' => $this->url,
            ]);

            return false;
        }
    }

    protected function buildPayload($data)
    {
        if ($this->payload_template) {
            // Use template to build payload
            $payload = [];
            foreach ($this->payload_template as $key => $template) {
                $payload[$key] = $this->resolveTemplate($template, $data);
            }

            return $payload;
        }

        // Default payload
        return [
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];
    }

    protected function resolveTemplate($template, $data)
    {
        // Simple template resolution
        // Could be enhanced with more complex logic
        if (is_string($template)) {
            return str_replace('{data}', json_encode($data), $template);
        }

        return $template;
    }

    public static function triggerForEvent($event, $data)
    {
        $webhooks = self::where('event', $event)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $webhook->trigger($data);
        }
    }
}
