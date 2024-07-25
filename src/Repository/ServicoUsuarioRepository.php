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
use App\Entity\ServicoUsuario;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUsuarioInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Repository\ServicoUsuarioRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ServicoUsuarioInterface>
 *
 * @method ServicoUsuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicoUsuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicoUsuario[]    findAll()
 * @method ServicoUsuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoUsuarioRepository extends ServiceEntityRepository implements ServicoUsuarioRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicoUsuario::class);
    }

    /** {@inheritdoc} */
    public function getAll(UsuarioInterface|int $usuario, UnidadeInterface|int $unidade): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(ServicoUsuario::class, 'e')
            ->join('e.servico', 's')
            ->where('e.usuario = :usuario')
            ->andWhere('e.unidade = :unidade')
            ->andWhere('s.ativo = TRUE')
            ->setParameter('usuario', $usuario)
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getResult();
    }

    /** {@inheritdoc} */
    public function get(
        UsuarioInterface|int $usuario,
        UnidadeInterface|int $unidade,
        ServicoInterface|int $servico,
    ): ?ServicoUsuarioInterface {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(ServicoUsuario::class, 'e')
            ->join('e.servico', 's')
            ->where('e.usuario = :usuario')
            ->andWhere('e.unidade = :unidade')
            ->andWhere('s = :servico')
            ->andWhere('s.ativo = TRUE')
            ->setParameter('usuario', $usuario)
            ->setParameter('unidade', $unidade)
            ->setParameter('servico', $servico)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
