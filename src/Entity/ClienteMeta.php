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

use App\Repository\ClienteMetadataRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\EntityMetadataInterface;

/**
 * ClienteMeta
 *
 * @implements EntityMetadataInterface<Cliente>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: ClienteMetadataRepository::class)]
#[ORM\Table(name: 'clientes_metadata')]
class ClienteMeta extends AbstractMetadata implements EntityMetadataInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'cliente_id', nullable: false)]
    protected ?Cliente $entity = null;
    
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
