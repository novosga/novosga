<?php
namespace core\business;

use \core\db\DB;

/**
 * PainelBusiness
 *
 * @author rogeriolino
 */
abstract class PainelBusiness {

    public static function paineis($unidade) {
        if ($unidade instanceof \core\model\Unidade) {
            $unidade = $unidade->getId();
        }
        $em = DB::getEntityManager();
        $query = $em->createQuery("SELECT e FROM \core\model\Painel e WHERE e.unidade = :unidade ORDER BY e.host");
        $query->setParameter('unidade', $unidade);
        return $query->getResult();
    }
    
    public static function painelInfo($unidade, $host) {
        $data = array();
        $em = DB::getEntityManager();
        $query = $em->createQuery("
            SELECT
                e 
            FROM 
                \core\model\Painel e 
                JOIN e.servicos s
            WHERE 
                e.unidade = :unidade AND
                e.host = :host
        ");
        if ($unidade instanceof \core\model\Unidade) {
            $unidade = $unidade->getId();
        }
        $query->setParameter('unidade', $unidade);
        $query->setParameter('host', $host);
        $painel = $query->getOneOrNullResult();
        if ($painel) {
            $ids = array();
            $data['ip'] = $painel->getIp();
            $data['unidade'] = $painel->getUnidade()->getNome();
            $data['servicos'] = array();
            foreach ($painel->getServicos() as $s) {
                $ids = $s->getServico()->getId();
                $data['servicos'][] = $s->getServico()->getNome();
            }
            // ultimas senhas
            $query = $em->createQuery("
                SELECT 
                    e 
                FROM 
                    \core\model\Atendimento e 
                    JOIN e.servicoUnidade su
                WHERE 
                    su.unidade = :unidade AND 
                    su.servico IN (:servicos) 
                ORDER BY
                    e.id DESC
            ");
            $query->setParameter('unidade', $unidade);
            $query->setParameter('servicos', $ids);
            $query->setMaxResults(5);
            $atendimentos = $query->getResult();
            $data['senhas'] = array();
            foreach ($atendimentos as $atendimento) {
                $data['senhas'][] = $atendimento->getSenha()->toString();
            }
        } else {
            throw new \Exception(_('Painel inválido'));
        }
        return $data;
    }
    
}
