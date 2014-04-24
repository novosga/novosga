<?php
namespace Novosga\Business;

use Doctrine\ORM\QueryBuilder;
use Novosga\Model\Servico;
use Novosga\Model\Unidade;
use Novosga\Model\Util\UsuarioSessao;

/**
 * FilaBusiness
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class FilaBusiness extends ModelBusiness {
    
    public static $orders = array(
        'time' => array(
            "((p.peso + 1) * (CURRENT_TIMESTAMP() - e.dataChegada))", "DESC"
        ),
        'priority' => array(
            "p.peso", "DESC"
        ),
        'number' => array(
            "e.numeroSenha", "ASC"
        )
    );
    
    
    /**
     * Retorna a fila de atendimento
     * @param mixed $unidade
     * @param array $servicos (ids dos servicos)
     * @param integer $tipo
     * @return QueryBuilder
     */
    public function atendimento($unidade, array $servicos, $tipo) {
        if ($unidade instanceof Unidade) {
            $unidade = $unidade->getId();
        }
        $cond = '';
        if ($tipo != UsuarioSessao::ATEND_TODOS) {
            $s = ($tipo == UsuarioSessao::ATEND_CONVENCIONAL) ? '=' : '>';
            $cond = " AND p.peso $s 0";
        }
        
        $builder = $this->builder()
                ->join('su.servico', 's')
                ->where("e.status = :status AND su.unidade = :unidade AND s.id IN (:servicos) $cond")
        ;
        
        $this->applyOrders($builder);
        
        $builder->setParameter('status', AtendimentoBusiness::SENHA_EMITIDA);
        $builder->setParameter('unidade', (int) $unidade);
        $builder->setParameter('servicos', $servicos);
        
        return $builder;
    }
    
    /**
     * 
     * @param mixed $unidade
     * @param mixed $servico
     * @return QueryBuilder
     */
    public function servico($unidade, $servico) {
        if ($unidade instanceof Unidade) {
            $unidade = $unidade->getId();
        }
        
        if ($servico instanceof Servico) {
            $servico = $servico->getId();
        }
        
        $builder = $this->builder()
                ->where("e.status = :status AND su.unidade = :unidade AND su.servico = :servico")
        ;
        
        $this->applyOrders($builder);
        
        $builder->setParameter('status', AtendimentoBusiness::SENHA_EMITIDA);
        $builder->setParameter('unidade', (int) $unidade);
        $builder->setParameter('servico', (int) $servico);
        
        return $builder;
    }
    
    
    /**
     * @return QueryBuilder
     */
    public function builder() {
        return $this->em
            ->createQueryBuilder()
            ->select('e')
            ->from('Novosga\Model\Atendimento', 'e')
            ->join('e.prioridadeSenha', 'p')
            ->join('e.servicoUnidade', 'su')
        ;
    }
    
    /**
     * Aplica a ordenação na QueryBuilder
     * @param QueryBuilder $builder
     */
    public function applyOrders(QueryBuilder $builder) {
        foreach (self::$orders as $order) {
            if (is_array($order)) {
                if (sizeof($order) === 1) {
                    $order[1] = "ASC";
                }
                $builder->addOrderBy($order[0], $order[1]);
            }
        }
    }
    
}
