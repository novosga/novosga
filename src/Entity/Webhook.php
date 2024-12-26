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

namespace App\Entity;

use App\EventListener\TimestampableEntityListener;
use App\Repository\WebhookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Webhook
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: WebhookRepository::class)]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
])]
#[ORM\Table(name: 'webhooks')]
class Webhook implements TimestampableEntityInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "webhook_id_seq", allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $url;

    /** @var array<string,string> Headers as key-value pairs */
    #[ORM\Column(type: Types::JSON)]
    private array $headers = [];

    /** @var string[] Events the webhook is subscribed to */
    #[ORM\Column(type: Types::JSON)]
    private array $events = [];

    #[ORM\Column]
    private bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /** @return array<string,string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** @param array<string,string> $headers */
    public function setHeaders(?array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /** @return string[] */
    public function getEvents(): array
    {
        return $this->events;
    }

    /** @param string[] $events */
    public function setEvents(array $events): static
    {
        $this->events = $events;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
