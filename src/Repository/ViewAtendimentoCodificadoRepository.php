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
use App\Entity\ViewAtendimentoCodificado;
use Novosga\Entity\AtendimentoCodificadoInterface;
use Novosga\Repository\ViewAtendimentoCodificadoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AtendimentoCodificadoInterface>
 *
 * @method ViewAtendimentoCodificado|null find($id, $lockMode = null, $lockVersion = null)
 * @method ViewAtendimentoCodificado|null findOneBy(array $criteria, array $orderBy = null)
 * @method ViewAtendimentoCodificado[]    findAll()
 * @method ViewAtendimentoCodificado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ViewAtendimentoCodificadoRepository extends ServiceEntityRepository implements
    ViewAtendimentoCodificadoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ViewAtendimentoCodificado::class);
    }
}
