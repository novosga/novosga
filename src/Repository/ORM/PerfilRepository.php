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
use Novosga\Entity\Perfil;
use Novosga\Repository\PerfilRepositoryInterface;

/**
 * PerfilRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PerfilRepository extends ServiceEntityRepository implements PerfilRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Perfil::class);
    }

    /**
     * Retorna todos os perfis ordenados pelo nível e pelo nome
     *
     * @return Perfil[]
     */
    public function findAll()
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
}
