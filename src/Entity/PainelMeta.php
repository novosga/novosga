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

namespace App\Entity;

use App\Repository\PainelMetadataRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\PainelInterface;

/**
 * Painel metadata.
 *
 * @implements EntityMetadataInterface<PainelInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: PainelMetadataRepository::class)]
#[ORM\Table(name: 'paineis_metadata')]
class PainelMeta extends AbstractMetadata implements EntityMetadataInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Painel::class)]
    #[ORM\JoinColumn(name: 'painel_id', nullable: false)]
    protected ?PainelInterface $entity = null;

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
