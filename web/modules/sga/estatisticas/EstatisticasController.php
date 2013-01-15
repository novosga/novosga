<?php
namespace modules\sga\estatisticas;

use \core\SGAContext;
use \core\model\Atendimento;
use \core\model\ViewAtendimento;
use \core\util\DateUtil;
use \core\http\AjaxResponse;
use \modules\sga\estatisticas\Relatorio;
use \core\controller\ModuleController;

/**
 * EstatisticasController
 *
 * @author rogeriolino
 */
class EstatisticasController extends ModuleController {
    
    const MAX_RESULTS = 1000;
    
    private $graficos;
    private $relatorios;
    
    public function __construct() {
        parent::__construct();
        $this->graficos = array(
            1 => new Grafico(_('Atendimentos por status'), 'pie'),
            2 => new Grafico(_('Atendimentos por serviço'), 'pie'),
            3 => new Grafico(_('Tempo médio do atendimento'), 'bar')
        );
        $this->relatorios = array(
            1 => new Relatorio(_('Serviços Disponíveis - Global'), 'servicos_disponiveis_global'),
            2 => new Relatorio(_('Serviços Disponíveis - Unidade'), 'servicos_disponiveis_unidades'),
            3 => new Relatorio(_('Atendimentos Concluídos'), 'atendimentos_concluidos'),
            4 => new Relatorio(_('Atendimentos em todos os status'), 'atendimentos_status')
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
        $this->view()->assign('graficos', $this->graficos);
        $this->view()->assign('statusAtendimento', Atendimento::situacoes());
    }
    
    public function grafico(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $id = (int) $context->getRequest()->getParameter('grafico');
            $dataInicial = $context->getRequest()->getParameter('inicial');
            $dataFinal = $context->getRequest()->getParameter('final');
            $dataInicial = DateUtil::formatToSQL($dataInicial);
            $dataFinal = DateUtil::formatToSQL($dataFinal);
            if (!isset($this->graficos[$id])) {
                throw new Exception(_('Gráfico inválido'));
            }
            $grafico = $this->graficos[$id];
            switch ($id) {
            case 1:
                $grafico->setLegendas(Atendimento::situacoes());
                $grafico->setDados($this->total_atendimentos_status($dataInicial, $dataFinal));
                break;
            case 2:
                $grafico->setDados($this->total_atendimentos_servico($dataInicial, $dataFinal));
                break;
            case 3:
                $grafico->setDados($this->tempo_medio_atendimentos($dataInicial, $dataFinal));
                break;
            }
            $response->data = $grafico->toArray();
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
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
            case 4:
                $relatorio->setDados($this->atendimentos_status($dataInicial, $dataFinal));
                break;
            }
            $this->view()->assign('relatorio', $relatorio);
        }
        $context->getResponse()->setRenderView(false);
    }
    
    private function total_atendimentos_status($dataInicial, $dataFinal) {
        $atendimentos = array();
        $status = Atendimento::situacoes();
        $dql = "
            SELECT 
                u.id as id,
                COUNT(e) as total 
            FROM 
                \core\model\ViewAtendimento e
                JOIN e.unidade u
            WHERE 
                e.dataChegada >= :inicio AND 
                e.dataChegada <= :fim
        ";
        // total
        $query = $this->em()->createQuery($dql . " GROUP BY u.id");
        $query->setParameter('inicio', $dataInicial);
        $query->setParameter('fim', $dataFinal);
        $rs = $query->getResult();
        foreach ($rs as $r) {
            $atendimentos[$r['id']] = array();
            // zerando os status
            foreach ($status as $k => $v) {
                $atendimentos[$r['id']][$k] = 0;
            }
        }
        // por status
        $query = $this->em()->createQuery($dql . " AND e.status = :status GROUP BY u.id");
        foreach ($status as $k => $v) {
            $query->setParameter('status', $k);
            $query->setParameter('inicio', $dataInicial);
            $query->setParameter('fim', $dataFinal);
            $rs = $query->getResult();
            foreach ($rs as $r) {
                $atendimentos[$r['id']][$k] = $r['total'];
            }
        }
        return $atendimentos;
    }
    
    private function total_atendimentos_servico($dataInicial, $dataFinal) {
        $atendimentos = array();
        $dql = "
            SELECT 
                u.id as id,
                s.nome as servico,
                COUNT(a) as total 
            FROM 
                \core\model\ViewAtendimento a
                JOIN a.unidade u
                JOIN a.servico s
            WHERE 
                a.dataChegada >= :inicio AND 
                a.dataChegada <= :fim
            GROUP BY 
                u, s
        ";
        $query = $this->em()->createQuery($dql);
        $query->setParameter('inicio', $dataInicial);
        $query->setParameter('fim', $dataFinal);
        $rs = $query->getResult();
        foreach ($rs as $r) {
            if (!isset($atendimentos[$r['id']])) {
                $atendimentos[$r['id']] = array();
            }
            $atendimentos[$r['id']][$r['servico']] = $r['total'];
        }
        return $atendimentos;
    }
    
    private function tempo_medio_atendimentos($dataInicial, $dataFinal) {
        $atendimentos = array();
        $dql = "
            SELECT 
                u.id as id,
                AVG(a.dataChamada - a.dataChegada) as espera,
                AVG(a.dataInicio - a.dataChamada) as deslocamento,
                AVG(a.dataFim - a.dataInicio) as atendimento,
                AVG(a.dataFim - a.dataChegada) as total
            FROM 
                \core\model\ViewAtendimento a
                JOIN a.unidade u
            WHERE 
                a.dataChegada >= :inicio AND 
                a.dataChegada <= :fim
            GROUP BY 
                u
        ";
        $query = $this->em()->createQuery($dql);
        $query->setParameter('inicio', $dataInicial);
        $query->setParameter('fim', $dataFinal);
        $rs = $query->getResult();
        foreach ($rs as $r) {
            if (!isset($atendimentos[$r['id']])) {
                $atendimentos[$r['id']] = array();
            }
            $atendimentos[$r['id']][_('Tempo de Espera')] = DateUtil::timeToSec($r['espera']);
            $atendimentos[$r['id']][_('Tempo de Deslocamento')] = DateUtil::timeToSec($r['deslocamento']);
            $atendimentos[$r['id']][_('Tempo de Atendimento')] = DateUtil::timeToSec($r['atendimento']);
            $atendimentos[$r['id']][_('Tempo Total')] = DateUtil::timeToSec($r['total']);
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
                    \core\model\ViewAtendimento e
                WHERE
                    e.unidade = :unidade AND
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
            $query->setMaxResults(self::MAX_RESULTS);
            $dados[$unidade->getId()] = array(
                'unidade' => $unidade->getNome(),
                'atendimentos' => $query->getResult()
            );
        }
        return $dados;
    }
    
    private function atendimentos_status($dataInicial, $dataFinal) {
        $unidades = $this->unidades();
        $dados = array();
        foreach ($unidades as $unidade) {
            $query = $this->em()->createQuery("
                SELECT
                    e
                FROM
                    \core\model\ViewAtendimento e
                WHERE
                    e.unidade = :unidade AND
                    e.dataChegada >= :dataInicial AND
                    e.dataChegada <= :dataFinal
                ORDER BY
                    e.numeroSenha
            ");
            $query->setParameter('unidade', $unidade);
            $query->setParameter('dataInicial', $dataInicial);
            $query->setParameter('dataFinal', $dataFinal);
            $query->setMaxResults(self::MAX_RESULTS);
            $dados[$unidade->getId()] = array(
                'unidade' => $unidade->getNome(),
                'atendimentos' => $query->getResult()
            );
        }
        return $dados;
    }
    
}
