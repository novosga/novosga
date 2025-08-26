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

namespace App\Dto;

use JsonSerializable;

/**
 * MercureEvent
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
final class MercureEvent implements JsonSerializable
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public readonly string $type,
        public readonly array $payload = [],
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return array_merge($this->payload, [
            '@type' => $this->type,
        ]);
    }
}
