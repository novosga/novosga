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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\MetadataInterface;

/**
 * Abstract metadata.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\MappedSuperclass]
abstract class AbstractMetadata implements MetadataInterface
{
    #[ORM\Id]
    #[ORM\Column(length: 30)]
    protected ?string $namespace = null;

    #[ORM\Id]
    #[ORM\Column(length: 30)]
    protected ?string $name = null;

    #[ORM\Column(type: Types::JSON)]
    protected mixed $value = null;

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(?string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'namespace' => $this->getName(),
            'name'      => $this->getNamespace(),
            'value'     => $this->getValue(),
        ];
    }
}
