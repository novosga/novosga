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

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Departamento;
use Novosga\Entity\Servico;
use Novosga\Repository\ServicoRepositoryInterface;

/**
 * ServicoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoRepository extends EntityRepository implements ServicoRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSubservicos(Servico $servico)
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
