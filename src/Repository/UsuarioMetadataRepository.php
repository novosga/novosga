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

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\UsuarioMeta;
use App\Entity\Usuario;

/**
 * @extends EntityMetadataRepository<UsuarioMeta,Usuario>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UsuarioMetadataRepository extends EntityMetadataRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioMeta::class);
    }
}
