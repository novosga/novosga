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
use App\Entity\Contador;
use Novosga\Entity\ContadorInterface;
use Novosga\Repository\ContadorRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ContadorInterface>
 *
 * @method Contador|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contador|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contador[]    findAll()
 * @method Contador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ContadorRepository extends ServiceEntityRepository implements ContadorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contador::class);
    }
}
