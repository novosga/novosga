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

use App\Service\TicketPrioritySwapService;
use Novosga\Event\TicketFirstReplyEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * TicketSubscriber
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class TicketSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TicketPrioritySwapService $service,
    ) {
    }

    public function onTicketFirstReplyEvent(TicketFirstReplyEvent $event): void
    {
        if (!$this->service->isEnabled()) {
            return;
        }

        $unidade = $event->atendimento->getUnidade();
        $usuario = $event->atendimento->getUsuario();

        if ($event->atendimento->getPrioridade()->getPeso() > 0) {
            $count = $this->service->getPriorityCount($unidade, $usuario) + 1;
        } else {
            $count = 0;
        }

        $this->service->setPriorityCount($unidade, $usuario, $count);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TicketFirstReplyEvent::class => 'onTicketFirstReplyEvent',
        ];
    }
}
