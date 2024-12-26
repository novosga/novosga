<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\MessageHandler;

use App\Message\SendWebhookMessage;
use App\Service\WebhookService;
use App\Types\WebhookEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * SendWebhookMessageHandler
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
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
