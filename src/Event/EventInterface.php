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
interface EventInterface
{
    public function getName(): string;

    public function getData(): mixed;
}
