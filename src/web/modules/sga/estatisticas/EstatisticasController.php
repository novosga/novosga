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
            $dataFinal = $context->getRequest()->getParameter('final') . ' 23:59:59';
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
                u.id as id,
                $columns
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
        $tempos = array(
            'espera' => _('Tempo de Espera'),
            'deslocamento' => _('Tempo de Deslocamento'),
            'atendimento' => _('Tempo de Atendimento'),
            'total' => _('Tempo Total')
        );
        foreach ($rs as $r) {
            if (!isset($atendimentos[$r['id']])) {
                $atendimentos[$r['id']] = array();
            }
            try {
                // se der erro tentando converter a data do banco para segundos, assume que ja esta em segundos
                foreach ($tempos as $k => $v) {
                    $atendimentos[$r['id']][$v] = DateUtil::timeToSec($r[$k]);
                }
            } catch (\Exception $e) {
                foreach ($tempos as $k => $v) {
                    $atendimentos[$r['id']][$v] = (int) $r[$k];
                }
            }
        }
        return $atendimentos;
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
                    e.dataInicio
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
                    e.dataInicio
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
