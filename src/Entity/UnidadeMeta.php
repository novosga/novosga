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

use App\Repository\UnidadeMetadataRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\UnidadeInterface;

/**
 * Unidade metadata.
 *
 * @implements EntityMetadataInterface<UnidadeInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: UnidadeMetadataRepository::class)]
#[ORM\Table(name: 'unidades_metadata')]
class UnidadeMeta extends AbstractMetadata implements EntityMetadataInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    #[ORM\JoinColumn(name: 'unidade_id', nullable: false)]
    protected ?UnidadeInterface $entity = null;

    public function setEntity($entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
