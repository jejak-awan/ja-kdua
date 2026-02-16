<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Services\Isp\ThirdParty\WhatsAppServiceInterface;
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
     * @param  mixed  $notifiable
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        /** @var mixed $toRaw */
        $toRaw = null;
        if (is_object($notifiable)) {
            $toRaw = property_exists($notifiable, 'phone') ? $notifiable->phone : (method_exists($notifiable, 'routeNotificationFor') ? $notifiable->routeNotificationFor('whatsapp') : null);
        }

        /** @var string|null $to */
        $to = is_string($toRaw) ? $toRaw : (is_numeric($toRaw) ? (string) $toRaw : null);

        if (! $to || ! is_string($message)) {
            return;
        }

        $this->whatsapp->send($to, $message);
    }
}
