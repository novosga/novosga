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

use App\Entity\ServicoMeta;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Repository\ServicoMetadataRepositoryInterface;

/**
 * @extends EntityMetadataRepository<EntityMetadataInterface<ServicoInterface>,ServicoInterface>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoMetadataRepository extends EntityMetadataRepository implements ServicoMetadataRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicoMeta::class);
    }
}
