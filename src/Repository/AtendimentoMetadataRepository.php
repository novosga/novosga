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
use App\Entity\AtendimentoMeta;
use App\Entity\Atendimento;

/**
 * @extends EntityMetadataRepository<AtendimentoMeta,Atendimento>
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
