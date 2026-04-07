<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Webhook;
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

    /** @return Webhook[] */
    public function findAll(): array
    {
        return $this
            ->repository
            ->findBy([], ['name' => 'ASC']);
    }

    public function save(Webhook $webhook): void
    {
        $this->repository->save($webhook);
    }

    public function remove(Webhook $webhook): void
    {
        $this->repository->remove($webhook);
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
