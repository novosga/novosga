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
use App\Entity\Servico;
use Novosga\Entity\ServicoInterface;
use Novosga\Repository\ServicoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ServicoInterface>
 *
 * @method Servico|null find($id, $lockMode = null, $lockVersion = null)
 * @method Servico|null findOneBy(array $criteria, array $orderBy = null)
 * @method Servico[]    findAll()
 * @method Servico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoRepository extends ServiceEntityRepository implements ServicoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Servico::class);
    }

    /** {@inheritdoc} */
    public function getSubservicos(ServicoInterface $servico): array
    {
        $subservicos = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(Servico::class, 'e')
            ->where('e.mestre = :mestre')
            ->andWhere('e.ativo = TRUE')
            ->andWhere('e.deletedAt IS NULL')
            ->orderBy('e.nome', 'ASC')
            ->setParameter('mestre', $servico)
            ->getQuery()
            ->getResult();

        return $subservicos;
    }
}
