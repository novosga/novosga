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

namespace App\Service;

use App\Repository\WebhookRepository;
use App\Types\WebhookEvent;

/**
 * WebhookService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebhookService
{
    public function __construct(
        private readonly WebhookDispatcher $dispatcher,
        private readonly WebhookRepository $repository,
    ) {
    }

    /** @param array<string,mixed> $payload */
    public function sendWebhook(WebhookEvent $event, array $payload): void
    {
        $webhooks = $this->repository->findEnabledByEvent($event);
        foreach ($webhooks as $webhook) {
            $this->dispatcher->dispatch(
                $event,
                $webhook->getUrl(),
                $webhook->getHeaders(),
                $payload,
            );
        }
    }
}
