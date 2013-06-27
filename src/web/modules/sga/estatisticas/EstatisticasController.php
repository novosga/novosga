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
            1 => new Grafico(_('Atendimentos por status'), 'pie', 'unidade,date'),
            2 => new Grafico(_('Atendimentos por serviço'), 'pie', 'unidade,date'),
            3 => new Grafico(_('Tempo médio do atendimento'), 'bar', 'unidade,date')
        );
        $this->relatorios = array(
            1 => new Relatorio(_('Serviços Disponíveis - Global'), 'servicos_disponiveis_global'),
            2 => new Relatorio(_('Serviços Disponíveis - Unidade'), 'servicos_disponiveis_unidades', 'unidade'),
            3 => new Relatorio(_('Atendimentos concluídos'), 'atendimentos_concluidos', 'unidade,date'),
            4 => new Relatorio(_('Atendimentos em todos os status'), 'atendimentos_status', 'unidade,date'),
            5 => new Relatorio(_('Lotações'), 'lotacoes', 'unidade'),
            6 => new Relatorio(_('Cargos'), 'cargos'),
        );
    }

    public function index(SGAContext $context) {
        $dir = MODULES_DIR . '/' . str_replace('.', '/', $context->getModulo()->getChave());
        $context->setParameter('js', array($dir . '/js/highcharts.js', $dir . '/js/highcharts.exporting.js'));
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e WHERE e.status = 1 ORDER BY e.nome");
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
            $dataFinal = $context->getRequest()->getParameter('final') . ' 23:59:59';
            $unidade = (int) $context->getRequest()->getParameter('unidade');
            $unidade = ($unidade > 0) ? $unidade : 0;
            if (!isset($this->graficos[$id])) {
                throw new Exception(_('Gráfico inválido'));
            }
            $grafico = $this->graficos[$id];
            switch ($id) {
            case 1:
                $grafico->setLegendas(Atendimento::situacoes());
                $grafico->setDados($this->total_atendimentos_status($dataInicial, $dataFinal, $unidade));
                break;
            case 2:
                $grafico->setDados($this->total_atendimentos_servico($dataInicial, $dataFinal, $unidade));
                break;
            case 3:
                $grafico->setDados($this->tempo_medio_atendimentos($dataInicial, $dataFinal, $unidade));
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
        $unidade = (int) $context->getRequest()->getParameter('unidade');
        $unidade = ($unidade > 0) ? $unidade : 0;
        if (isset($this->relatorios[$id])) {
            $relatorio = $this->relatorios[$id];
            $this->view()->assign('dataInicial', DateUtil::format($dataInicial, _('d/m/Y')));
            $this->view()->assign('dataFinal', DateUtil::format($dataFinal, _('d/m/Y')));
            $dataFinal = $dataFinal . ' 23:59:59';
            switch ($id) {
            case 1:
                $relatorio->setDados($this->servicos_disponiveis_global());
                break;
            case 2:
                $relatorio->setDados($this->servicos_disponiveis_unidade($unidade));
                break;
            case 3:
                $relatorio->setDados($this->atendimentos_concluidos($dataInicial, $dataFinal, $unidade));
                break;
            case 4:
                $relatorio->setDados($this->atendimentos_status($dataInicial, $dataFinal, $unidade));
                break;
            case 5:
                $relatorio->setDados($this->lotacoes($unidade));
                break;
            case 6:
                $relatorio->setDados($this->cargos());
                break;
            }
            $this->view()->assign('relatorio', $relatorio);
        }
        $context->getResponse()->setRenderView(false);
    }
    
    private function unidades() {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e WHERE e.status = 1 ORDER BY e.nome");
        return $query->getResult();
    }
    
    private function unidadesArray($default = 0) {
        if ($default == 0) {
            return $this->unidades();
        } else {
            $unidade = $this->em()->find('\core\model\Unidade', $default);
            if (!$unidade) {
                throw new \Exception('Invalid parameter');
            }
            return array($unidade);
        }
    }
    
    private function total_atendimentos_status($dataInicial, $dataFinal, $unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
        $dados = array();
        $status = Atendimento::situacoes();
        $query = $this->em()->createQuery("
            SELECT 
                COUNT(e) as total 
            FROM 
                \core\model\ViewAtendimento e
            WHERE 
                e.dataChegada >= :inicio AND 
                e.dataChegada <= :fim AND
                e.unidade = :unidade AND 
                e.status = :status
        ");
        $query->setParameter('inicio', $dataInicial);
        $query->setParameter('fim', $dataFinal);
        foreach ($unidades as $unidade) {
            $dados[$unidade->getId()] = array();
            // pegando todos os status
            foreach ($status as $k => $v) {
                $query->setParameter('unidade', $unidade->getId());
                $query->setParameter('status', $k);
                $rs = $query->getSingleResult();
                $dados[$unidade->getId()][$k] = (int) $rs['total'];
            }
        }
        return $dados;
    }
    
    private function total_atendimentos_servico($dataInicial, $dataFinal, $unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
        $dados = array();
        $query = $this->em()->createQuery("
            SELECT 
                s.nome as servico,
                COUNT(a) as total 
            FROM 
                \core\model\ViewAtendimento a
                JOIN a.unidade u
                JOIN a.servico s
            WHERE 
                a.status = :status AND
                a.dataChegada >= :inicio AND 
                a.dataChegada <= :fim AND
                a.unidade = :unidade
            GROUP BY 
                s
        ");
        $query->setParameter('status', Atendimento::ATENDIMENTO_ENCERRADO_CODIFICADO);
        $query->setParameter('inicio', $dataInicial);
        $query->setParameter('fim', $dataFinal);
        foreach ($unidades as $unidade) {
            $query->setParameter('unidade', $unidade->getId());
            $rs = $query->getResult();
            $dados[$unidade->getId()] = array();
            foreach ($rs as $r) {
                $dados[$unidade->getId()][$r['servico']] = $r['total'];
            }
        }
        return $dados;
    }
    
    private function tempo_medio_atendimentos($dataInicial, $dataFinal, $unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
        $dados = array();
        $tempos = array(
            'espera' => _('Tempo de Espera'),
            'deslocamento' => _('Tempo de Deslocamento'),
            'atendimento' => _('Tempo de Atendimento'),
            'total' => _('Tempo Total')
        );
        $columns = '';
        // quando SQL Server usa a função DATEDIFF (registrada na classe DB)
        if (\core\Config::DB_TYPE == 'mssql') {
            $columns = '
                AVG(DATEDIFF(a.dataChegada, a.dataChamada)) as espera,
                AVG(DATEDIFF(a.dataChamada, a.dataInicio)) as deslocamento,
                AVG(DATEDIFF(a.dataInicio, a.dataFim)) as atendimento,
                AVG(DATEDIFF(a.dataChegada, a.dataFim)) as total
            ';
        } else {
            $columns = '
                AVG(a.dataChamada - a.dataChegada) as espera,
                AVG(a.dataInicio - a.dataChamada) as deslocamento,
                AVG(a.dataFim - a.dataInicio) as atendimento,
                AVG(a.dataFim - a.dataChegada) as total
            ';
        }
        $dql = "
            SELECT 
                $columns
            FROM 
                \core\model\ViewAtendimento a
                JOIN a.unidade u
            WHERE 
                a.dataChegada >= :inicio AND 
                a.dataChegada <= :fim AND
                a.unidade = :unidade
        ";
        $query = $this->em()->createQuery($dql);
        $query->setParameter('inicio', $dataInicial);
        $query->setParameter('fim', $dataFinal);
        foreach ($unidades as $unidade) {
            $query->setParameter('unidade', $unidade->getId());
            $rs = $query->getResult();
            $dados[$unidade->getId()] = array();
            foreach ($rs as $r) {
                try {
                    // se der erro tentando converter a data do banco para segundos, assume que ja esta em segundos
                    foreach ($tempos as $k => $v) {
                        $dados[$unidade->getId()][$v] = DateUtil::timeToSec($r[$k]);
                    }
                } catch (\Exception $e) {
                    foreach ($tempos as $k => $v) {
                        $dados[$unidade->getId()][$v] = (int) $r[$k];
                    }
                }
            }
        }
        return $dados;
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
    private function servicos_disponiveis_unidade($unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
        $dados = array();
        foreach ($unidades as $unidade) {
            $query = $this->em()->createQuery("
                SELECT
                    e
                FROM
                    \core\model\ServicoUnidade e
                    JOIN e.servico s
                    LEFT JOIN s.subServicos sub
                WHERE
                    s.mestre IS NULL AND
                    e.status = 1 AND
                    e.unidade = :unidade 
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
    
    private function atendimentos_concluidos($dataInicial, $dataFinal, $unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
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
                    e.dataChegada
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
    
    private function atendimentos_status($dataInicial, $dataFinal, $unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
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
                    e.dataChegada
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
    
    /**
     * Retorna todos os usuarios e cargos (lotação) por unidade
     * @return array
     */
    private function lotacoes($unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
        $dados = array();
        $query = $this->em()->createQuery("
            SELECT
                l
            FROM
                \core\model\Lotacao l
                LEFT JOIN l.usuario u
                LEFT JOIN l.grupo g
                LEFT JOIN l.cargo c
            WHERE
                g.left <= (
                    SELECT g2.left FROM \core\model\Grupo g2 WHERE g2.id = (SELECT u2g.id FROM \core\model\Unidade u2 INNER JOIN u2.grupo u2g WHERE u2.id = :unidade)
                ) AND
                g.right >= (
                    SELECT g3.right FROM \core\model\Grupo g3 WHERE g3.id = (SELECT u3g.id FROM \core\model\Unidade u3 INNER JOIN u3.grupo u3g WHERE u3.id = :unidade)
                )
            ORDER BY
                u.login
        ");
        foreach ($unidades as $unidade) {
            $query->setParameter('unidade', $unidade);
            $dados[$unidade->getId()] = array(
                'unidade' => $unidade->getNome(),
                'lotacoes' => $query->getResult()
            );
        }
        return $dados;
    }
    
    /**
     * Retorna todos os cargos e suas permissões
     * @return array
     */
    private function cargos() {
        $dados = array();
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Cargo e ORDER BY e.nome");
        $cargos = $query->getResult();
        foreach ($cargos as $cargo) {
            $dados[$cargo->getId()] = array(
                'cargo' => $cargo->getNome(),
                'permissoes' => $cargo->getPermissoes()
            );
        }
        return $dados;
    }

}
