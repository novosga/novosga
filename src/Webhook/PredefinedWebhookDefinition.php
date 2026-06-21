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

namespace App\Webhook;

use App\Types\WebhookEvent;

/**
 * PredefinedWebhookDefinition
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PredefinedWebhookDefinition
{
    /** @param string[] $events */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $description,
        public readonly string $homepage,
        public readonly string $url,
        public readonly array $events,
        public readonly ?string $logo = null,
    ) {
    }

    /** @return self[] */
    public static function all(): array
    {
        return [
            new self(
                key: 'mangati-monitor',
                name: 'Mangati Monitor',
                description: 'admin.webhook.predefined.mangati_monitor.description',
                homepage: 'https://monitor.mangati.com',
                url: 'https://monitor.mangati.com/webhook/novosga',
                events: [
                    WebhookEvent::TICKET_CALLED->value,
                    WebhookEvent::TICKET_CANCELED->value,
                    WebhookEvent::TICKET_CREATED->value,
                    WebhookEvent::TICKET_FINISHED->value,
                    WebhookEvent::TICKET_FIRST_REPLY->value,
                    WebhookEvent::TICKET_NO_SHOW->value,
                    WebhookEvent::TICKET_REACTIVE->value,
                    WebhookEvent::TICKET_REDIRECTED->value,
                    WebhookEvent::TICKET_START->value,
                    WebhookEvent::TICKET_TRANSFERRED->value,
                ],
                logo: 'images/webhooks/mangati-monitor.svg',
            ),
            new self(
                key: 'mangati-avaliacao',
                name: 'Mangati Avaliação',
                description: 'admin.webhook.predefined.mangati_avaliacao.description',
                homepage: 'https://avaliacao.mangati.com',
                url: 'https://avaliacao.mangati.com/webhook/novosga',
                events: [
                    WebhookEvent::TICKET_FINISHED->value,
                ],
                logo: 'images/webhooks/mangati-avaliacao.svg',
            ),
            new self(
                key: 'mangati-notificacao',
                name: 'Mangati Noticação',
                description: 'admin.webhook.predefined.mangati_notificacao.description',
                homepage: 'https://avaliacao.mangati.com',
                url: 'https://notificacao.mangati.com/webhook/novosga',
                events: [
                    WebhookEvent::TICKET_CREATED->value,
                    WebhookEvent::TICKET_FIRST_REPLY->value,
                ],
                logo: 'images/webhooks/mangati-notificacao.svg',
            ),
        ];
    }

    public static function find(string $key): ?self
    {
        foreach (self::all() as $definition) {
            if ($definition->key === $key) {
                return $definition;
            }
        }

        return null;
    }
}
