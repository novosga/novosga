<?php

namespace Novosga\Service;

use Doctrine\ORM\QueryBuilder;
use Novosga\Config\AppConfig;
use Novosga\Entity\Servico;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Entity\Atendimento;
use Novosga\Entity\ServicoUsuario;

/**
 * FilaService.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class FilaService extends ModelService
{

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
    public function filaAtendimento(Unidade $unidade, $servicosUsuario, $tipoAtendimento = 1, $maxResults = 0)
    {
        $usuario = null;
        
        $ids = [0];
        foreach ($servicosUsuario as $servico) {
            $usuario = $servico->getUsuario();
            $ids[] = $servico->getServico()->getId();
        }
        
        $where = '';
        // se nao atende todos, filtra pelo tipo de atendimento
        if ($tipoAtendimento !== 1) {
            $s = ($tipoAtendimento === 2) ? '=' : '>';
            $where = "p.peso $s 0";
        }
        
        $builder = $this->builder($usuario)
                        ->andWhere('e.status = :status')
                        ->andWhere('su.unidade = :unidade')
                        ->andWhere('s.id IN (:servicos)');
        
        $params = [
            'status' => AtendimentoService::SENHA_EMITIDA,
            'unidade' => $unidade,
            'servicos' => $ids
        ];
        
        if ($usuario) {
            $builder->join(ServicoUsuario::class, 'servicoUsuario', 'WITH', 'servicoUsuario.servico = s AND servicoUsuario.usuario = :usuario');
            $params['usuario'] = $usuario;
        }
        
        if (!empty($where)) {
            $builder->andWhere($where);
        }

        $this->applyOrders($builder, $unidade, $usuario);

        $query = $builder
                    ->setParameters($params)
                    ->getQuery();

        if ($maxResults > 0) {
            $query->setMaxResults($maxResults);
        }

        return $query->getResult();
    }

    /**
     * Retorna a fila de espera do serviço na unidade.
     *
     * @param Unidade $unidade
     * @param Servico $servico
     * @param Usuario $usuario
     *
     * @return array
     */
    public function filaServico(Unidade $unidade, Servico $servico)
    {
        $builder = $this->builder();
        
        $params = [
            'status' => AtendimentoService::SENHA_EMITIDA,
            'unidade' => $unidade,
            'servico' => $servico
        ];
        
        $builder
                ->where('e.status = :status')
                ->andWhere('su.unidade = :unidade')
                ->andWhere('su.servico = :servico');
        
        $this->applyOrders($builder, $unidade);

        $builder->setParameters($params);

        return $builder->getQuery()->getResult();
    }

    /**
     * @return QueryBuilder
     */
    public function builder(Usuario $usuario = null)
    {
        $qb = $this->em
            ->createQueryBuilder()
            ->select('e')
            ->from(Atendimento::class, 'e')
            ->join('e.prioridade', 'p')
            ->join('e.servicoUnidade', 'su')
            ->join('su.servico', 's');
        
        return $qb;
    }

    /**
     * Aplica a ordenação na QueryBuilder.
     *
     * @param QueryBuilder $builder
     */
    public function applyOrders(QueryBuilder $builder, Unidade $unidade, Usuario $usuario = null)
    {
        $ordering = AppConfig::getInstance()->get('queue.ordering');
        if (is_callable($ordering)) {
            $ordering = $ordering($unidade, $usuario);
        }
        if (!$ordering || empty($ordering)) {
            $ordering = [
                // priority
                [
                    'exp'   => 'p.peso',
                    'order' => 'DESC',
                ]
            ];
            if ($usuario) {
                // peso servico x usuario
                $ordering[] = [
                    'exp'   => 'servicoUsuario.peso',
                    'order' => 'ASC',
                ];
            }
            
            // ticket number
            $ordering[] = [
                'exp'   => 'e.numeroSenha',
                'order' => 'ASC',
            ];
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
