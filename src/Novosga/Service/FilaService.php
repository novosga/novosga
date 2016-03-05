<?php

namespace Novosga\Service;

use Doctrine\ORM\QueryBuilder;
use Novosga\Config\AppConfig;
use Novosga\Entity\Servico;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Entity\ServicoUsuario;

/**
 * FilaService.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class FilaService extends ModelService
{
    // default queue ordering
    public static $ordering = [
        // wait time
        [
            'exp'   => '((p.peso + 1) * (CURRENT_TIMESTAMP() - e.dataChegada))',
            'order' => 'DESC',
        ],
        // priority
        [
            'exp'   => 'p.peso',
            'order' => 'DESC',
        ],
        // ticket number
        [
            'exp'   => 'e.numeroSenha',
            'order' => 'ASC',
        ],
    ];

    /**
     * Retorna a fila de atendimentos do usuario.
     *
     * @param Unidade          $unidade
     * @param ServicoUsuario[] $servicosUsuario
     * @param int              $tipoAtendimento
     * @param int              $maxResults
     *
     * @return array
     */
    public function atendimentos(Unidade $unidade, $servicosUsuario, $tipoAtendimento = 1, $maxResults = 0)
    {
        $ids = [0];
        foreach ($servicosUsuario as $servico) {
            $ids[] = $servico->getServico()->getId();
        }
        
        $where = '';
        // se nao atende todos, filtra pelo tipo de atendimento
        if ($tipoAtendimento !== 1) {
            $s = ($tipoAtendimento === 2) ? '=' : '>';
            $where = "p.peso $s 0";
        }
        
        $builder = $this->builder()
                        ->andWhere('e.status = :status')
                        ->andWhere('su.unidade = :unidade')
                        ->andWhere('s.id IN (:servicos)');
        
        if (!empty($where)) {
            $builder->andWhere($where);
        }

        $this->applyOrders($builder, $unidade);

        $query = $builder
                    ->setParameters([
                        'status' => AtendimentoService::SENHA_EMITIDA,
                        'unidade' => $unidade,
                        'servicos' => $ids
                    ])
                    ->getQuery();

        if ($maxResults > 0) {
            $query->setMaxResults($maxResults);
        }

        return $query->getResult();
    }

    /**
     * Retorna a fila de espera do serviço na unidade.
     *
     * @param mixed $unidade
     * @param mixed $servico
     *
     * @return array
     */
    public function filaServico($unidade, $servico)
    {
        if (!($unidade instanceof Unidade)) {
            $unidade = $this->em->find('Novosga\Entity\Unidade', $unidade);
        }

        if (!($servico instanceof Servico)) {
            $servico = $this->em->find('Novosga\Entity\Servico', $servico);
        }

        $builder = $this->builder()
                ->where('e.status = :status AND su.unidade = :unidade AND su.servico = :servico');

        $this->applyOrders($builder, $unidade);

        $builder->setParameter('status', AtendimentoService::SENHA_EMITIDA);
        $builder->setParameter('unidade', $unidade);
        $builder->setParameter('servico', $servico);

        return $builder->getQuery()->getResult();
    }

    /**
     * @return QueryBuilder
     */
    public function builder()
    {
        return $this->em
            ->createQueryBuilder()
            ->select('e')
            ->from('Novosga\Entity\Atendimento', 'e')
            ->join('e.prioridade', 'p')
            ->join('e.servicoUnidade', 'su')
            ->join('su.servico', 's')
            ->join('e.usuarioTriagem', 'ut')
            ->leftJoin('e.usuario', 'u');
    }

    /**
     * Aplica a ordenação na QueryBuilder.
     *
     * @param QueryBuilder $builder
     */
    public function applyOrders(QueryBuilder $builder, Unidade $unidade)
    {
        $ordering = AppConfig::getInstance()->get('queue.ordering');
        if (is_callable($ordering)) {
            $ordering = $ordering($unidade);
        }
        if (!$ordering || empty($ordering)) {
            $ordering = self::$ordering;
        }
        foreach ($ordering as $item) {
            if (!isset($item['exp'])) {
                break;
            }
            $exp = $item['exp'];
            $order = isset($item['order']) ? $item['order'] : 'ASC';
            $builder->addOrderBy($exp, $order);
        }
    }
}
