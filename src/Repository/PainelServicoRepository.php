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

use App\Entity\PainelServico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\PainelServicoInterface;
use Novosga\Repository\PainelServicoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<PainelServicoInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PainelServicoRepository extends ServiceEntityRepository implements PainelServicoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PainelServico::class);
    }
}
