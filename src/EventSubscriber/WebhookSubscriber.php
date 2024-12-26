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

use App\Message\SendWebhookMessage;
use App\Types\WebhookEvent;
use Novosga\Event\TicketCalledEvent;
use Novosga\Event\TicketCanceledEvent;
use Novosga\Event\TicketCreatedEvent;
use Novosga\Event\TicketFinishedEvent;
use Novosga\Event\TicketFirstReplyEvent;
use Novosga\Event\TicketNoShowEvent;
use Novosga\Event\TicketReactivedEvent;
use Novosga\Event\TicketRedirectedEvent;
use Novosga\Event\TicketStartEvent;
use Novosga\Event\TicketTransferedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * WebhookSubscriber
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebhookSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onTicketChange(object $ticketEvent): void
    {
        $webhookEvent = $this->getWebhookEvent($ticketEvent);
        if (!$webhookEvent) {
            return;
        }
        $payload = $this->getWebhookPayload($ticketEvent);

        $message = new SendWebhookMessage(
            event: $webhookEvent->value,
            payload: $payload,
        );
        try {
            $this->bus->dispatch($message);
        } catch (TransportException $ex) {
            $this->logger->error('Error sending webhook', [
                'message' => $message,
                'exception' => $ex,
            ]);
        }
    }

    private function getWebhookEvent(mixed $event): ?WebhookEvent
    {
        return match (true) {
            $event instanceof TicketCalledEvent => WebhookEvent::TICKET_CALLED,
            $event instanceof TicketCanceledEvent => WebhookEvent::TICKET_CANCELED,
            $event instanceof TicketCreatedEvent => WebhookEvent::TICKET_CREATED,
            $event instanceof TicketFinishedEvent => WebhookEvent::TICKET_FINISHED,
            $event instanceof TicketFirstReplyEvent => WebhookEvent::TICKET_FIRST_REPLY,
            $event instanceof TicketNoShowEvent => WebhookEvent::TICKET_NO_SHOW,
            $event instanceof TicketReactivedEvent => WebhookEvent::TICKET_REACTIVE,
            $event instanceof TicketRedirectedEvent => WebhookEvent::TICKET_REDIRECTED,
            $event instanceof TicketStartEvent => WebhookEvent::TICKET_START,
            $event instanceof TicketTransferedEvent => WebhookEvent::TICKET_TRANSFERED,
            default => null
        };
    }

    /** @return array<string,mixed> */
    private function getWebhookPayload(mixed $event): array
    {
        return match (true) {
            $event instanceof TicketCalledEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketCanceledEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketCreatedEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketFinishedEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketFirstReplyEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketNoShowEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketReactivedEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketRedirectedEvent => [
                $event->atendimentoAnterior->jsonSerialize(),
                $event->atendimentoNovo->jsonSerialize()
            ],
            $event instanceof TicketStartEvent => $event->atendimento->jsonSerialize(),
            $event instanceof TicketTransferedEvent => $event->atendimento->jsonSerialize(),
            default => []
        };
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TicketCalledEvent::class => 'onTicketChange',
            TicketCanceledEvent::class => 'onTicketChange',
            TicketCreatedEvent::class => 'onTicketChange',
            TicketFinishedEvent::class => 'onTicketChange',
            TicketFirstReplyEvent::class => 'onTicketChange',
            TicketNoShowEvent::class => 'onTicketChange',
            TicketReactivedEvent::class => 'onTicketChange',
            TicketRedirectedEvent::class => 'onTicketChange',
            TicketStartEvent::class => 'onTicketChange',
            TicketTransferedEvent::class => 'onTicketChange',
        ];
    }
}
