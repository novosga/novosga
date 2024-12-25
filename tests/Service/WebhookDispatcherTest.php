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

use App\Service\WebhookDispatcher;
use App\Types\WebhookEvent;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * WebhookDispatcherTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebhookDispatcherTest extends KernelTestCase
{
    private const URL = 'https://novosga.org/webhook';
    private const EVENT_NAME = WebhookEvent::TICKET_CREATED;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDispatchSuccess(): void
    {
        $headers = ['Authorization' => 'Bearer token'];
        $payload = ['id' => 1, 'name' => 'Test'];

        $httpClient = new MockHttpClient([
            function ($method, $url, $options) use ($payload): MockResponse {
                $this->assertSame('POST', $method);
                $this->assertSame(self::URL, $url);

                $body = $options['body'];
                $headers = $options['headers'];
                $this->assertIsArray($headers);
                $this->assertSame($body, json_encode($payload));
                $this->assertContains('Content-Type: application/json', $headers);
                $this->assertContains('Authorization: Bearer token', $headers);
                $this->assertContains('x-webhook-event: ' . self::EVENT_NAME->value, $headers);

                return new MockResponse('', ['http_code' => 200]);
            }
        ]);

        /** @var NullLogger&MockObject */
        $logger = $this->createMock(NullLogger::class);
        $logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Webhook dispatched successfully'));

        $dispatcher = new WebhookDispatcher($httpClient, $logger);

        $this->assertTrue($dispatcher->dispatch(
            self::EVENT_NAME,
            self::URL,
            $headers,
            $payload,
        ));
    }

    public function testDispatchFailure(): void
    {
        $headers = ['Authorization' => 'Bearer token'];
        $payload = ['id' => 1, 'name' => 'Test'];

        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 500]),
        ]);

        /** @var NullLogger&MockObject */
        $logger = $this->createMock(NullLogger::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Webhook dispatch failed'));

        $dispatcher = new WebhookDispatcher($httpClient, $logger);

        $this->assertFalse($dispatcher->dispatch(
            self::EVENT_NAME,
            self::URL,
            $headers,
            $payload,
        ));
    }

    public function testDispatchTransportException(): void
    {
        $headers = ['Authorization' => 'Bearer token'];
        $payload = ['id' => 1, 'name' => 'Test'];

        $httpClient = new MockHttpClient([
            function ($method, $url, $options): MockResponse {
                throw new TransportException();
            }
        ]);

        /** @var NullLogger&MockObject */
        $logger = $this->createMock(NullLogger::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('HTTP transport error during webhook dispatch'));

        $dispatcher = new WebhookDispatcher($httpClient, $logger);

        $this->assertFalse($dispatcher->dispatch(
            self::EVENT_NAME,
            self::URL,
            $headers,
            $payload,
        ));
    }
}
