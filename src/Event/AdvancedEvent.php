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
 * AdvancedEvent
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AdvancedEvent extends Event implements
    LoggerAwareEventInterface,
    StorageAwareEventInterface,
    UserAwareEventInterface
{
    use LoggerAwareTrait;
    use StorageAwareTrait;
    use UserAwareTrait;
}
