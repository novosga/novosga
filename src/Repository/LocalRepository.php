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
use App\Entity\Local;
use Novosga\Entity\LocalInterface;
use Novosga\Repository\LocalRepositoryInterface;

/**
 * @extends ServiceEntityRepository<LocalInterface>
 *
 * @method Local|null find($id, $lockMode = null, $lockVersion = null)
 * @method Local|null findOneBy(array $criteria, array $orderBy = null)
 * @method Local[]    findAll()
 * @method Local[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class LocalRepository extends ServiceEntityRepository implements LocalRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Local::class);
    }
}
