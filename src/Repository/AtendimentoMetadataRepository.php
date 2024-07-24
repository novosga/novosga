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

use App\Entity\AtendimentoMeta;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\AtendimentoInterface;

/**
 * @extends EntityMetadataRepository<AtendimentoMeta,AtendimentoInterface>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class AtendimentoMetadataRepository extends EntityMetadataRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AtendimentoMeta::class);
    }
}
