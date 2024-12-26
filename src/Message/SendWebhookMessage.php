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

namespace App\Message;

/**
 * SendWebhookMessage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
final class SendWebhookMessage
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public readonly string $event,
        public readonly array $payload,
    ) {
    }
}
