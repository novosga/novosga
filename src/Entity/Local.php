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

use App\EventListener\LocalListener;
use App\EventListener\TimestampableEntityListener;
use App\Repository\LocalRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\LocalInterface;

/**
 * Local de atendimento
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: LocalRepository::class)]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
    LocalListener::class,
])]
#[ORM\Table(name: 'locais')]
class Local implements TimestampableEntityInterface, LocalInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "locais_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $nome = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setNome(?string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function __toString()
    {
        return $this->getNome();
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->getId(),
            'nome'      => $this->getNome(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d\TH:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d\TH:i:s') : null,
        ];
    }
}
