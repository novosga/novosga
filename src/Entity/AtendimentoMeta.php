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

use App\Repository\AtendimentoMetadataRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\EntityMetadataInterface;

/**
 * AtendimentoMeta.
 *
 * @implements EntityMetadataInterface<AtendimentoInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: AtendimentoMetadataRepository::class)]
#[ORM\Table(name: 'atendimentos_metadata')]
class AtendimentoMeta extends AbstractMetadata implements EntityMetadataInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Atendimento::class)]
    #[ORM\JoinColumn(name: 'atendimento_id', nullable: false)]
    protected ?AtendimentoInterface $entity = null;

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
