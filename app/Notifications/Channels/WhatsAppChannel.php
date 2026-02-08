<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Services\WhatsApp\WhatsAppServiceInterface;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    protected WhatsAppServiceInterface $whatsapp;

    public function __construct(WhatsAppServiceInterface $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Send the given notification.
     * 
     * @param mixed $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        
        // Try to get phone from notifiable, prioritie 'phone' attribute
        $to = $notifiable->phone ?? $notifiable->routeNotificationFor('whatsapp');

        if (! $to || ! is_string($message)) {
            return;
        }

        $this->whatsapp->send($to, $message);
    }
}
