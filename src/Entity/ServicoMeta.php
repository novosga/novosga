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
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\ServicoInterface;

/**
 * Servico metadata.
 *
 * @implements EntityMetadataInterface<ServicoInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'servicos_metadata')]
class ServicoMeta extends AbstractMetadata implements EntityMetadataInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Servico::class)]
    #[ORM\JoinColumn(name: 'servico_id', nullable: false)]
    protected ?ServicoInterface $entity = null;

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
