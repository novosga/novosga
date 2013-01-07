<?php
namespace modules\sga\estatisticas;

use \core\SGAContext;
use \core\util\DateUtil;
use \core\model\Unidade;
use \core\controller\ModuleController;

/**
 * EstatisticasController
 *
 * @author rogeriolino
 */
class EstatisticasController extends ModuleController {

    public function index(SGAContext $context) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        $this->view()->assign('unidades', $unidades);
        $ini = DateUtil::now('Y-m-d');
        $fim = DateUtil::nowSQL(); // full datetime
        $this->view()->assign('atendimentosStatus', $this->total_atendimentos_status($ini, $fim));
        $this->view()->assign('atendimentosServico', $this->total_atendimentos_servico($ini, $fim));
    }
    
    private function total_atendimentos_status($dataInicial, $dataFinal) {
        $atendimentos = array();
        $status = array(
            'encerrado' => \core\model\Atendimento::ATENDIMENTO_ENCERRADO,
            'nao_compareceu' => \core\model\Atendimento::NAO_COMPARECEU,
            'senha_cancelada' => \core\model\Atendimento::SENHA_CANCELADA,
            'erro_triagem' => \core\model\Atendimento::ERRO_TRIAGEM
        );
        $sql = "
            SELECT 
                id_uni as id,
                COUNT(*) as total 
            FROM 
                view_historico_atendimentos
            WHERE 
                dt_cheg >= :dtini AND 
                dt_cheg <= :dtfim
        ";
        // total
        $conn = $this->em()->getConnection();
        $stmt = $conn->prepare($sql . " GROUP BY id_uni");
        $stmt->bindValue('dtini', $dataInicial);
        $stmt->bindValue('dtfim', $dataFinal);
        $stmt->execute();
        $rs = $stmt->fetchAll();
        foreach ($rs as $r) {
            $atendimentos[$r['id']] = array();
            // zerando os status
            foreach ($status as $k => $v) {
                $atendimentos[$r['id']][$k] = 0;
            }
        }
        // por status
        $sql .= " AND id_stat = :status GROUP BY id_uni";
        // encerrado
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('dtini', $dataInicial);
        $stmt->bindValue('dtfim', $dataFinal);
        foreach ($status as $k => $v) {
            $stmt->bindValue('status', $v);
            $stmt->execute();
            $rs = $stmt->fetchAll();
            foreach ($rs as $r) {
                $atendimentos[$r['id']][$k] = $r['total'];
            }
        }
        return $atendimentos;
    }
    
    private function total_atendimentos_servico($dataInicial, $dataFinal) {
        $atendimentos = array();
        $sql = "
            SELECT 
                a.id_uni as id,
                us.nm_serv as servico,
                COUNT(*) as total 
            FROM 
                view_historico_atendimentos a
                INNER JOIN
                    uni_serv us
                    ON 
                        us.id_uni = a.id_uni AND us.id_serv = a.id_serv
            WHERE 
                a.dt_cheg >= :dtini AND 
                a.dt_cheg <= :dtfim
            GROUP BY 
                a.id_uni, a.id_serv, us.nm_serv
        ";
        $conn = $this->em()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('dtini', $dataInicial);
        $stmt->bindValue('dtfim', $dataFinal);
        $stmt->execute();
        $rs = $stmt->fetchAll();
        foreach ($rs as $r) {
            if (!isset($atendimentos[$r['id']])) {
                $atendimentos[$r['id']] = array();
            }
            $atendimentos[$r['id']][$r['servico']] = $r['total'];
        }
        return $atendimentos;
    }
    
}
