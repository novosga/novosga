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
use App\Entity\Departamento;
use Novosga\Entity\DepartamentoInterface;
use Novosga\Repository\DepartamentoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<DepartamentoInterface>
 *
 * @method Departamento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Departamento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Departamento[]    findAll()
 * @method Departamento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class DepartamentoRepository extends ServiceEntityRepository implements DepartamentoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Departamento::class);
    }
}
