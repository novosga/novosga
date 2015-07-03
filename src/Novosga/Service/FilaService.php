<?php

namespace Novosga\Service;

use Doctrine\ORM\QueryBuilder;
use Novosga\Config\AppConfig;
use Novosga\Model\Servico;
use Novosga\Model\Unidade;
use Novosga\Model\Util\UsuarioSessao;

/**
 * FilaService.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class FilaService extends ModelService
{
    // default queue ordering
    public static $ordering = array(
        // wait time
        array(
            'exp' => '((p.peso + 1) * (CURRENT_TIMESTAMP() - e.dataChegada))',
            'order' => 'DESC',
        ),
        // priority
        array(
            'exp' => 'p.peso',
            'order' => 'DESC',
        ),
        // ticket number
        array(
            'exp' => 'e.numeroSenha',
            'order' => 'ASC',
        ),
    );

    /**
     * Retorna a fila de atendimentos do usuario.
     *
     * @param UsuarioSessao $usuario
     * @param int           $maxResults
     *
     * @return array
     */
    public function atendimentos(UsuarioSessao $usuario, $maxResults = 0)
    {
        $ids = array(0);
        $servicos = $usuario->getServicos();
        foreach ($servicos as $s) {
            $ids[] = $s->getServico()->getId();
        }
        $cond = '';
        // se nao atende todos, filtra pelo tipo de atendimento
        if ($usuario->getTipoAtendimento() != UsuarioSessao::ATEND_TODOS) {
            $s = ($usuario->getTipoAtendimento() == UsuarioSessao::ATEND_CONVENCIONAL) ? '=' : '>';
            $cond = "p.peso $s 0";
            $rs = $this->atendimentosUsuario($usuario, $ids, $maxResults, $cond);
        } else {
            // se atende todos mas tem limite para sequencia de tipo de atendimento
            $maxPrioridade = (int) AppConfig::getInstance()->get('queue.limits.priority');
            if ($maxPrioridade > 0 && $usuario->getSequenciaPrioridade() > 0 && $usuario->getSequenciaPrioridade() % $maxPrioridade === 0) {
                $cond = 'p.peso = 0';
            }
            $rs = $this->atendimentosUsuario($usuario, $ids, $maxResults, $cond);
            // se a lista veio vazia, tenta pegar qualquer um
            if (sizeof($rs) === 0) {
                $rs = $this->atendimentosUsuario($usuario, $ids, $maxResults);
            }
        }

        return $rs;
    }

    private function atendimentosUsuario(UsuarioSessao $usuario, $servicos, $maxResults = 0, $where = '')
    {
        $builder = $this->builder()
                ->where('e.status = :status AND su.unidade = :unidade AND s.id IN (:servicos)')
        ;
        if (!empty($where)) {
            $builder->andWhere($where);
        }

        $this->applyOrders($builder, $usuario->getUnidade());

        $query = $builder->getQuery()
                ->setParameter('status', AtendimentoService::SENHA_EMITIDA)
                ->setParameter('unidade', $usuario->getUnidade()->getId())
                ->setParameter('servicos', $servicos)
        ;

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
            $unidade = $this->em->find('Novosga\Model\Unidade', $unidade);
        }

        if (!($servico instanceof Servico)) {
            $servico = $this->em->find('Novosga\Model\Servico', $servico);
        }

        $builder = $this->builder()
                ->where('e.status = :status AND su.unidade = :unidade AND su.servico = :servico')
        ;

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
            ->from('Novosga\Model\Atendimento', 'e')
            ->join('e.prioridade', 'p')
            ->join('e.servicoUnidade', 'su')
            ->join('su.servico', 's')
            ->join('e.usuarioTriagem', 'ut')
            ->leftJoin('e.usuario', 'u')
        ;
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
