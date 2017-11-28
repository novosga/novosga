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
use Novosga\Entity\Atendimento;
use Novosga\Entity\Servico;
use Novosga\Entity\Unidade;
use Novosga\Repository\AtendimentoRepositoryInterface;

/**
 * AtendimentoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class AtendimentoRepository extends EntityRepository implements AtendimentoRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function countByServicos(Unidade $unidade, array $servicos, $status = null)
    {
        $rs = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('s.id, COUNT(e) as total')
            ->from(Atendimento::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->andWhere('e.servico IN (:servicos)')
            ->andWhere('(:status IS NULL OR e.status = :status)')
            ->groupBy('s.id')
            ->setParameters([
                'unidade'  => $unidade,
                'servicos' => $servicos,
                'status'   => $status,
            ])
            ->getQuery()
            ->getArrayResult();
        
        return $rs;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUltimo(Unidade $unidade, Servico $servico = null)
    {
        $atendimento = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(Atendimento::class, 'e')
            ->where('e.unidade = :unidade')
            ->andWhere('(:servico IS NULL OR e.servico = :servico)')
            ->orderBy('e.id', 'DESC')
            ->setParameters([
                'servico' => $servico,
                'unidade' => $unidade
            ])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        
        return $atendimento;
    }
}
