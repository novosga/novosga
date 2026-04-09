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

use App\Entity\Painel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\PainelInterface;
use Novosga\Repository\PainelRepositoryInterface;

/**
 * @extends ServiceEntityRepository<PainelInterface>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PainelRepository extends ServiceEntityRepository implements PainelRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Painel::class);
    }

    public function findByUnidade(UnidadeInterface|int $unidade): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.unidade = :unidade')
            ->setParameter('unidade', $unidade)
            ->orderBy('p.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
