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

namespace App\EventSubscriber;

use Novosga\Event\QueueOrderingEvent;
use Novosga\Service\QueueOrderingServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * QueueOrderingSubscriber
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class QueueOrderingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly QueueOrderingServiceInterface $service,
    ) {
    }

    public function onQueueOrdering(QueueOrderingEvent $event): void
    {
        $this->service->applyOrder(
            $event->queryBuilder,
            $event->unidade,
            $event->usuario,
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QueueOrderingEvent::class => 'onQueueOrdering',
        ];
    }
}
