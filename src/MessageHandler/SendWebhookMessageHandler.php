<?php

namespace App\MessageHandler;

use App\Message\SendWebhookMessage;
use App\Service\WebhookService;
use App\Types\WebhookEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendWebhookMessageHandler
{
    public function __construct(
        private readonly WebhookService $service,
    ) {
    }

    public function __invoke(SendWebhookMessage $message): void
    {
        $event = WebhookEvent::tryFrom($message->event);
        if (!$event) {
            return;
        }

        $this->service->sendWebhook($event, $message->payload);
    }
}
