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

use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\EntityMetadataInterface;

/**
 * AtendimentoMeta (Historico).
 *
 * @implements EntityMetadataInterface<AtendimentoInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'historico_atendimentos_metadata')]
class AtendimentoHistoricoMeta extends AbstractMetadata implements EntityMetadataInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AtendimentoHistorico::class)]
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
