<?php

namespace App\Message;

final class SendWebhookMessage
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public readonly string $event,
        public readonly array $payload,
    ) {
    }
}
