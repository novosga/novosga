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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ViewAtendimento;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Repository\ViewAtendimentoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AtendimentoInterface>
 *
 * @method ViewAtendimento|null find($id, $lockMode = null, $lockVersion = null)
 * @method ViewAtendimento|null findOneBy(array $criteria, array $orderBy = null)
 * @method ViewAtendimento[]    findAll()
 * @method ViewAtendimento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ViewAtendimentoRepository extends ServiceEntityRepository implements ViewAtendimentoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ViewAtendimento::class);
    }
}
