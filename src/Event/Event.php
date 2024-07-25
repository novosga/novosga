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

namespace App\Event;

/**
 * Event
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Event implements EventInterface
{
    public function __construct(
        private string $name,
        private mixed $data,
    ) {
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->name;
    }

    /** {@inheritdoc} */
    public function getData(): mixed
    {
        return $this->data;
    }
}
