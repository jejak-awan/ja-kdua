<?php

declare(strict_types=1);

namespace App\Events\Isp;

use App\Models\Isp\Support\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProvisioningUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceRequest $request;
    public string $step;
    public string $status;
    public string $message;

    /**
     * Create a new event instance.
     */
    public function __construct(ServiceRequest $request, string $step, string $status, string $message)
    {
        $this->request = $request;
        $this->step = $step;
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("provisioning.{$this->request->id}"),
            new PrivateChannel("customer.{$this->request->customer_id}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'provisioning.update';
    }
}
