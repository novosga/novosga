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
use App\Entity\Cliente;
use Novosga\Entity\ClienteInterface;
use Novosga\Repository\ClienteRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ClienteInterface>
 *
 * @method Cliente|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cliente|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cliente[]    findAll()
 * @method Cliente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ClienteRepository extends ServiceEntityRepository implements ClienteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    /**
     * Retorna todos os clientes ordenados pelo nome
     * @return Cliente[]
     */
    public function findAll(): array
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }

    /** @return Cliente[] */
    public function findByDocumento(string $documento): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.documento LIKE :documento')
            ->setParameter('documento', $documento)
            ->getQuery()
            ->getResult();
    }
}
