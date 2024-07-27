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

namespace App\EventSubscriber;

use App\Service\Configuration;
use Novosga\Event\QueueOrderingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * QueueOrderingSubscriber
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class QueueOrderingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Configuration $config,
        private readonly KernelInterface $kernel,
    ) {
    }

    public function onQueueOrdering(QueueOrderingEvent $event): void
    {
        $ordering = $this->config->get('queue.ordering');
        if (is_callable($ordering)) {
            $ordering($event, $this->kernel->getContainer());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QueueOrderingEvent::class => 'onQueueOrdering',
        ];
    }
}
