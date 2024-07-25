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
 * EventDispatcherInterface
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
interface EventDispatcherInterface
{
    /**
     * @param EventInterface $event
     * @return bool
     */
    public function dispatch(EventInterface $event): bool;

    /**
     *
     * @param string $eventName
     * @param mixed  $eventData
     * @param bool   $advancedEvent
     * @return bool
     */
    public function createAndDispatch(string $eventName, $eventData, bool $advancedEvent = false): bool;
}
