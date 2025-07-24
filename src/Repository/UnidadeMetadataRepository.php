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

namespace App\Repository;

use App\Entity\UnidadeMeta;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Repository\UnidadeMetadataRepositoryInterface;

/**
 * @extends EntityMetadataRepository<EntityMetadataInterface<UnidadeInterface>,UnidadeInterface>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UnidadeMetadataRepository extends EntityMetadataRepository implements UnidadeMetadataRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnidadeMeta::class);
    }
}
