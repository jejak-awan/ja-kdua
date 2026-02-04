<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendBroadcastNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array{type: string, title: string, message: string, target_type: string, target_id?: int|string|null, sender_id?: int|string|null} */
    protected array $payload;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = [
            'type' => is_string($t = $payload['type'] ?? null) ? $t : 'info',
            'title' => is_string($t = $payload['title'] ?? null) ? $t : 'Notification',
            'message' => is_string($m = $payload['message'] ?? null) ? $m : '',
            'target_type' => is_string($t = $payload['target_type'] ?? null) ? $t : 'user',
            'target_id' => (is_int($id = $payload['target_id'] ?? null) || is_string($id)) ? $id : null,
            'sender_id' => (is_int($id = $payload['sender_id'] ?? null) || is_string($id)) ? $id : null,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $type = $this->payload['type'];
        $title = $this->payload['title'];
        $message = $this->payload['message'];
        $targetType = $this->payload['target_type'];
        $targetId = $this->payload['target_id'] ?? null;
        $senderId = $this->payload['sender_id'] ?? null;

        try {
            if ($targetType === 'all') {
                User::chunk(500, function ($users) use ($type, $title, $message, $senderId) {
                    foreach ($users as $user) {
                        Notification::createForUser(
                            $user->id,
                            $type,
                            $title,
                            $message,
                            null,
                            null,
                            ['sender_id' => $senderId, 'is_broadcast' => true]
                        );
                    }
                });
            } elseif ($targetType === 'role') {
                /** @var array<int, int|string> $userIds */
                $userIds = DB::table('model_has_roles')
                    ->where('role_id', intval($targetId))
                    ->pluck('model_id')
                    ->all();

                foreach ($userIds as $recipientId) {
                    Notification::createForUser(
                        intval($recipientId),
                        $type,
                        $title,
                        $message,
                        null,
                        null,
                        ['sender_id' => $senderId, 'is_broadcast' => true]
                    );
                }
            } elseif ($targetType === 'user') {
                Notification::createForUser(
                    intval($targetId ?? 0),
                    $type,
                    $title,
                    $message,
                    null,
                    null,
                    ['sender_id' => $senderId, 'is_broadcast' => true]
                );
            }
        } catch (\Exception $e) {
            Log::error('Broadcast job error: '.$e->getMessage());
            throw $e;
        }
    }
}
