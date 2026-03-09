<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Services\NotificationService;

class SendMessageNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        // Envoyer les notifications aux participants
        $this->notificationService->notifyNewMessage($event->message);
    }
}
