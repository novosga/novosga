<?php

namespace App\Types;

/**
 * WebhookEvent
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
enum WebhookEvent: string
{
    case TICKET_CALLED = 'ticket.called';
    case TICKET_CANCELED = 'ticket.canceled';
    case TICKET_CREATED = 'ticket.created';
    case TICKET_FINISHED = 'ticket.finished';
    case TICKET_FIRST_REPLY = 'ticket.first_reply';
    case TICKET_NO_SHOW = 'ticket.no_show';
    case TICKET_REACTIVE = 'ticket.reactive';
    case TICKET_REDIRECTED = 'ticket.redirected';
    case TICKET_START = 'ticket.start';
    case TICKET_TRANSFERED = 'ticket.transfered';
}
