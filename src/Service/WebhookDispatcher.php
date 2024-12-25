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

use App\Types\WebhookEvent;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * WebhookDispatcher
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebhookDispatcher
{
    private const HEADER_EVENT_NAME = 'x-webhook-event';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Dispatch a webhook to its target URL.
     *
     * @param array<string,string> $headers Headers as key-value pairs
     * @param array<string,string> $payload The payload to send in the request.
     * @return bool Returns true on success, false on failure.
     */
    public function dispatch(WebhookEvent $event, string $url, array $headers, array $payload): bool
    {
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => array_merge($headers, [self::HEADER_EVENT_NAME => $event->value]),
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logger->info('Webhook dispatched successfully', [
                    'url' => $url,
                    'status_code' => $statusCode,
                ]);
                return true;
            }

            $this->logger->error('Webhook dispatch failed', [
                'url' => $url,
                'status_code' => $statusCode,
                'response' => $response->getContent(false),
            ]);

            return false;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('HTTP transport error during webhook dispatch', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
