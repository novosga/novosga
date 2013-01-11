<?php
namespace modules\sga\estatisticas;

use \core\SGAContext;
use \core\util\DateUtil;
use \core\util\Strings;
use \modules\sga\estatisticas\Relatorio;
use \core\controller\ModuleController;

/**
 * EstatisticasController
 *
 * @author rogeriolino
 */
class EstatisticasController extends ModuleController {
    
    private $relatorios;
    
    public function __construct() {
        parent::__construct();
        $this->relatorios = array(
            1 => new Relatorio(_('Serviços Disponíveis - Global'), 'servicos_disponiveis_global'),
            2 => new Relatorio(_('Serviços Disponíveis - Unidade'), 'servicos_disponiveis_unidades'),
            3 => new Relatorio(_('Atendimentos Concluídos'), 'atendimentos_concluidos'),
            4 => new Relatorio(_('Atendimentos em todos os status'), 'atendimentos_status'),
            5 => new Relatorio(_('Senhas por Status'), 'senhas_status')
        );
    }

    public function index(SGAContext $context) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        $this->view()->assign('unidades', $unidades);
        $ini = DateUtil::now('Y-m-d');
        $fim = DateUtil::nowSQL(); // full datetime
        $this->view()->assign('atendimentosStatus', $this->total_atendimentos_status($ini, $fim));
        $this->view()->assign('atendimentosServico', $this->total_atendimentos_servico($ini, $fim));
        $this->view()->assign('relatorios', $this->relatorios);
    }
    
    public function relatorio(SGAContext $context) {
        $id = (int) $context->getRequest()->getParameter('relatorio');
        $dataInicial = $context->getRequest()->getParameter('inicial');
        $dataFinal = $context->getRequest()->getParameter('final');
        if (isset($this->relatorios[$id])) {
            $relatorio = $this->relatorios[$id];
            //DateUtils::formatToSQL();
            $this->view()->assign('dataInicial', $dataInicial);
            $this->view()->assign('dataFinal', $dataFinal);
            switch ($id) {
            case 1:
                $relatorio->setDados($this->servicos_disponiveis_global());
                break;
            case 2:
                $relatorio->setDados($this->servicos_disponiveis_unidade());
                break;
            case 3:
                $relatorio->setDados($this->atendimentos_concluidos($dataInicial, $dataFinal));
                break;
            }
            $this->view()->assign('relatorio', $relatorio);
        }
        $context->getResponse()->setRenderView(false);
    }
    
    private function total_atendimentos_status($dataInicial, $dataFinal) {
        $atendimentos = array();
        $status = array(
            'encerrado' => array(\core\model\Atendimento::ATENDIMENTO_ENCERRADO, \core\model\Atendimento::ATENDIMENTO_ENCERRADO_CODIFICADO),
            'nao_compareceu' => array(\core\model\Atendimento::NAO_COMPARECEU),
            'senha_cancelada' => array(\core\model\Atendimento::SENHA_CANCELADA),
            'erro_triagem' => array(\core\model\Atendimento::ERRO_TRIAGEM)
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
        $sql .= " AND id_stat IN ({status}) GROUP BY id_uni";
        foreach ($status as $k => $v) {
            $stmt = $conn->prepare(Strings::format($sql, array('status' => join(',', $v))));
            $stmt->bindValue('dtini', $dataInicial);
            $stmt->bindValue('dtfim', $dataFinal);
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
    
    private function unidades() {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e WHERE e.status = 1 ORDER BY e.nome");
        return $query->getResult();
    }
    
    private function servicos_disponiveis_global() {
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                \core\model\Servico e
                LEFT JOIN e.subServicos sub
            WHERE
                e.mestre IS NULL
            ORDER BY
                e.nome
        ");
        return $query->getResult();
    }
    
    /**
     * Retorna todos os servicos disponiveis para cada unidade
     * @return array
     */
    private function servicos_disponiveis_unidade() {
        $unidades = $this->unidades();
        $dados = array();
        foreach ($unidades as $unidade) {
            $query = $this->em()->createQuery("
                SELECT
                    e
                FROM
                    \core\model\Servico e
                    LEFT JOIN e.subServicos sub
                WHERE
                    e.mestre IS NULL AND
                    e IN (
                        SELECT s FROM \core\model\ServicoUnidade su JOIN su.servico s WHERE su.unidade = :unidade
                    )
                ORDER BY
                    e.nome
            ");
            $query->setParameter('unidade', $unidade);
            $dados[$unidade->getId()] = array(
                'unidade' => $unidade->getNome(),
                'servicos' => $query->getResult()
            );
        }
        return $dados;
    }
    
    private function atendimentos_concluidos($dataInicial, $dataFinal) {
        $unidades = $this->unidades();
        $dados = array();
        foreach ($unidades as $unidade) {
            $query = $this->em()->createQuery("
                SELECT
                    e
                FROM
                    \core\model\Atendimento e
                    JOIN e.servicoUnidade su
                WHERE
                    su.unidade = :unidade AND
                    e.status = :status AND
                    e.dataChegada >= :dataInicial AND
                    e.dataChegada <= :dataFinal
                ORDER BY
                    e.numeroSenha
            ");
            $query->setParameter('unidade', $unidade);
            $query->setParameter('status', \core\model\Atendimento::ATENDIMENTO_ENCERRADO_CODIFICADO);
            $query->setParameter('dataInicial', $dataInicial);
            $query->setParameter('dataFinal', $dataFinal);
            $dados[$unidade->getId()] = array(
                'unidade' => $unidade->getNome(),
                'atendimentos' => $query->getResult()
            );
        }
        return $dados;
    }
    
}
