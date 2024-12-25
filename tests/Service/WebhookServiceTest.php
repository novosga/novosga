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

namespace App\Tests\Service;

use App\Entity\Webhook;
use App\Repository\WebhookRepository;
use App\Service\WebhookService;
use App\Types\WebhookEvent;
use App\Service\WebhookDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * WebhookServiceTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebhookServiceTest extends TestCase
{
    private WebhookService $service;
    private WebhookDispatcher&MockObject $dispatcher;
    private WebhookRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(WebhookDispatcher::class);
        $this->repository = $this->createMock(WebhookRepository::class);
        $this->service = new WebhookService($this->dispatcher, $this->repository);
    }

    public function testSendWebhook(): void
    {
        $event = WebhookEvent::TICKET_CALLED;
        $payload = ['key' => 'value'];
        $webhook1 = (new Webhook())
            ->setUrl('https://novosga.org/webhook1')
            ->setHeaders(['Content-Type' => 'application/json']);
        $webhook2 = (new Webhook())
            ->setUrl('https://novosga.org/webhook2')
            ->setHeaders(['Another' => 'header']);

        $this->repository
            ->expects($this->once())
            ->method('findEnabledByEvent')
            ->with($event)
            ->willReturn([$webhook1, $webhook2]);

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $event,
                    'https://novosga.org/webhook1',
                    ['Content-Type' => 'application/json'],
                    $payload,
                ],
                [
                    $event,
                    'https://novosga.org/webhook2',
                    ['Another' => 'header'],
                    $payload,
                ]
            );

        $this->service->sendWebhook($event, $payload);
    }
}
