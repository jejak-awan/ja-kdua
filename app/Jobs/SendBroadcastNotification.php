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

    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
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
                $userIds = DB::table('model_has_roles')
                    ->where('role_id', $targetId)
                    ->pluck('model_id');

                foreach ($userIds as $recipientId) {
                    Notification::createForUser(
                        $recipientId,
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
                    $targetId,
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
