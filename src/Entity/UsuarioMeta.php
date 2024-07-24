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

use App\Repository\UsuarioMetadataRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\UsuarioInterface;

/**
 * Usuario metadata.
 *
 * @implements EntityMetadataInterface<UsuarioInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: UsuarioMetadataRepository::class)]
#[ORM\Table(name: 'usuarios_metadata')]
class UsuarioMeta extends AbstractMetadata implements EntityMetadataInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_id', nullable: false)]
    protected ?UsuarioInterface $entity = null;

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
