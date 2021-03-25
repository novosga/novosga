<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\ServicoUsuario;
use Novosga\Repository\ServicoUsuarioRepositoryInterface;

/**
 * ServicoUsuarioRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoUsuarioRepository extends ServiceEntityRepository implements ServicoUsuarioRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicoUsuario::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAll($usuario, $unidade)
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
            ->setParameters([
                'usuario' => $usuario,
                'unidade' => $unidade,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function get($usuario, $unidade, $servico)
    {
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
            ->setParameters([
                'usuario' => $usuario,
                'unidade' => $unidade,
                'servico' => $servico,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
