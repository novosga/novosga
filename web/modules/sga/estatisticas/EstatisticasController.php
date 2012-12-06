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
    
    public function __construct() {
        $this->title = _('Estatisticas');
        $this->subtitle = _('Visualize e exporte estastísticas e relatórios sobre o sistema');
    }

    public function index(SGAContext $context) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        $this->view()->assign('unidades', $unidades);
        $now = DateUtil::nowSQL();
        $this->view()->assign('atendimentos', $this->total_atendimentos($now, $now));
    }
    
    private function total_atendimentos($dataInicial, $dataFinal) {
        $atendimentos = array();
        $dql = "
            SELECT 
                u.id as id,
                COUNT(e) as total 
            FROM 
                \core\model\Atendimento e 
                JOIN e.servicoUnidade su 
                JOIN su.unidade u 
            WHERE 
                e.dataChegada >= :dtini AND 
                e.dataChegada <= :dtfim
        ";
        // total
        $query = $this->em()->createQuery($dql . " GROUP BY u");
        $query->setParameter('dtini', $dataInicial);
        $query->setParameter('dtfim', $dataFinal);
        $rs = $query->getResult();
        foreach ($rs as $r) {
            $atendimentos[$r['id']] = array('total' => $r['total'], 'encerrado' => 0);
        }
        // encerrado
        $query = $this->em()->createQuery($dql . " AND e.status = :status GROUP BY u");
        $query->setParameter('dtini', $dataInicial);
        $query->setParameter('dtfim', $dataFinal);
        $query->setParameter('status', \core\model\Atendimento::ATENDIMENTO_ENCERRADO);
        $rs = $query->getResult();
        foreach ($rs as $r) {        
            $atendimentos[$r['id']]['encerrado'] = $r['total'];
        }
        return $atendimentos;
    }
    
}
