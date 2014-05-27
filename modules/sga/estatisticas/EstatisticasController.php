<?php
namespace modules\sga\estatisticas;

use \Novosga\SGA;
use \Novosga\SGAContext;
use \Novosga\Business\AtendimentoBusiness;
use \Novosga\Model\Modulo;
use \Novosga\Util\DateUtil;
use \Novosga\Http\AjaxResponse;
use \modules\sga\estatisticas\Relatorio;
use \Novosga\Controller\ModuleController;

/**
 * EstatisticasController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class EstatisticasController extends ModuleController {
    
    const MAX_RESULTS = 1000;
    
    private $graficos;
    private $relatorios;
    
    public function __construct(SGA $app, Modulo $modulo) {
        parent::__construct($app, $modulo);
        $this->graficos = array(
            1 => new Grafico(_('Atendimentos por status'), 'pie', 'unidade,date'),
            2 => new Grafico(_('Atendimentos por serviço'), 'pie', 'unidade,date'),
            3 => new Grafico(_('Tempo médio do atendimento'), 'bar', 'unidade,date')
        );
        $this->relatorios = array(
            1 => new Relatorio(_('Serviços Disponíveis - Global'), 'servicos_disponiveis_global'),
            2 => new Relatorio(_('Serviços Disponíveis - Unidade'), 'servicos_disponiveis_unidades', 'unidade'),
            3 => new Relatorio(_('Serviços codificados'), 'servicos_codificados', 'unidade,date'),
            4 => new Relatorio(_('Atendimentos concluídos'), 'atendimentos_concluidos', 'unidade,date'),
            5 => new Relatorio(_('Atendimentos em todos os status'), 'atendimentos_status', 'unidade,date'),
            6 => new Relatorio(_('Tempos médios por Atendente'), 'tempo_medio_atendentes', 'date'),
            7 => new Relatorio(_('Lotações'), 'lotacoes', 'unidade'),
            8 => new Relatorio(_('Cargos'), 'cargos'),
        );
    }

    public function index(SGAContext $context) {
        $dir = MODULES_DIR . '/' . $context->getModulo()->getChave();
        $context->setParameter('js', array(__DIR__ . '/js/highcharts.js', __DIR__ . '/js/highcharts.exporting.js'));
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Unidade e WHERE e.status = 1 ORDER BY e.nome");
        $unidades = $query->getResult();
        $this->app()->view()->set('unidades', $unidades);
        $this->app()->view()->set('relatorios', $this->relatorios);
        $this->app()->view()->set('graficos', $this->graficos);
        $this->app()->view()->set('statusAtendimento', AtendimentoBusiness::situacoes());
        $arr = array();
        foreach ($unidades as $u) {
            $arr[$u->getId()] = $u->getNome();
        }
        $this->app()->view()->set('unidadesJson', json_encode($arr));
        $this->app()->view()->set('now', \Novosga\Util\DateUtil::now(_('d/m/Y')));
    }
    
    /**
     * Retorna os gráficos do dia a partir da unidade informada
     */
    public function today(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $ini = DateUtil::now('Y-m-d');
            $fim = DateUtil::nowSQL(); // full datetime
            $unidade = (int) $context->request()->getParameter('unidade');
            $status = $this->total_atendimentos_status($ini, $fim, $unidade);
            $response->data['legendas'] = AtendimentoBusiness::situacoes();
            $response->data['status'] = $status[$unidade];
            $servicos = $this->total_atendimentos_servico($ini, $fim, $unidade);
            $response->data['servicos'] = $servicos[$unidade];
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function grafico(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $id = (int) $context->request()->getParameter('grafico');
            $dataInicial = $context->request()->getParameter('inicial');
            $dataFinal = $context->request()->getParameter('final') . ' 23:59:59';
            $unidade = (int) $context->request()->getParameter('unidade');
            $unidade = ($unidade > 0) ? $unidade : 0;
            if (!isset($this->graficos[$id])) {
                throw new Exception(_('Gráfico inválido'));
            }
            $grafico = $this->graficos[$id];
            switch ($id) {
            case 1:
                $grafico->setLegendas(AtendimentoBusiness::situacoes());
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
        $context->response()->jsonResponse($response);
    }
    
    public function relatorio(SGAContext $context) {
        $id = (int) $context->request()->getParameter('relatorio');
        $dataInicial = $context->request()->getParameter('inicial');
        $dataFinal = $context->request()->getParameter('final');
        $unidade = (int) $context->request()->getParameter('unidade');
        $unidade = ($unidade > 0) ? $unidade : 0;
        if (isset($this->relatorios[$id])) {
            $relatorio = $this->relatorios[$id];
            $this->app()->view()->set('dataInicial', DateUtil::format($dataInicial, _('d/m/Y')));
            $this->app()->view()->set('dataFinal', DateUtil::format($dataFinal, _('d/m/Y')));
            $dataFinal = $dataFinal . ' 23:59:59';
            switch ($id) {
            case 1:
                $relatorio->setDados($this->servicos_disponiveis_global());
                break;
            case 2:
                $relatorio->setDados($this->servicos_disponiveis_unidade($unidade));
                break;
            case 3:
                $relatorio->setDados($this->servicos_codificados($dataInicial, $dataFinal, $unidade));
                break;
            case 4:
                $relatorio->setDados($this->atendimentos_concluidos($dataInicial, $dataFinal, $unidade));
                break;
            case 5:
                $relatorio->setDados($this->atendimentos_status($dataInicial, $dataFinal, $unidade));
                break;
            case 6:
                $relatorio->setDados($this->tempo_medio_atendentes($dataInicial, $dataFinal));
                break;
            case 7:
                $relatorio->setDados($this->lotacoes($unidade));
                break;
            case 8:
                $relatorio->setDados($this->cargos());
                break;
            }
            $this->app()->view()->set('relatorio', $relatorio);
        }
        $this->app()->view()->set('page', "relatorios/{$relatorio->getArquivo()}.html.twig");
        $this->app()->view()->set('isNumeracaoServico', AtendimentoBusiness::isNumeracaoServico());
        
        // filtros
        $this->app()->view()->getInstance()->addFilter(new \Novosga\Twig\SecFormat());
        
        $context->response()->setRenderView(false);
    }
    
    private function unidades() {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Unidade e WHERE e.status = 1 ORDER BY e.nome");
        return $query->getResult();
    }
    
    private function unidadesArray($default = 0) {
        if ($default == 0) {
            return $this->unidades();
        } else {
            $unidade = $this->em()->find('Novosga\Model\Unidade', $default);
            if (!$unidade) {
                throw new \Exception('Invalid parameter');
            }
            return array($unidade);
        }
    }
    
    private function total_atendimentos_status($dataInicial, $dataFinal, $unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
        $dados = array();
        $status = AtendimentoBusiness::situacoes();
        $query = $this->em()->createQuery("
            SELECT 
                COUNT(e) as total 
            FROM 
                Novosga\Model\ViewAtendimento e
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
                Novosga\Model\ViewAtendimento a
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
        $query->setParameter('status', AtendimentoBusiness::ATENDIMENTO_ENCERRADO_CODIFICADO);
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
        $dql = "
            SELECT 
                AVG(a.dataChamada - a.dataChegada) as espera,
                AVG(a.dataInicio - a.dataChamada) as deslocamento,
                AVG(a.dataFim - a.dataInicio) as atendimento,
                AVG(a.dataFim - a.dataChegada) as total
            FROM 
                Novosga\Model\ViewAtendimento a
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
                    // Isso é necessário para manter a compatibilidade entre os bancos
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
                Novosga\Model\Servico e
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
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\ServicoUnidade e
                JOIN e.servico s
                LEFT JOIN s.subServicos sub
            WHERE
                s.mestre IS NULL AND
                e.status = 1 AND
                e.unidade = :unidade 
            ORDER BY
                e.nome
        ");
        foreach ($unidades as $unidade) {
            $query->setParameter('unidade', $unidade);
            $dados[$unidade->getId()] = array(
                'unidade' => $unidade->getNome(),
                'servicos' => $query->getResult()
            );
        }
        return $dados;
    }
    
    private function servicos_codificados($dataInicial, $dataFinal, $unidadeId = 0) {
        $unidades = $this->unidadesArray($unidadeId);
        $query = $this->em()->createQuery("
            SELECT
                COUNT(c) as total,
                s.nome
            FROM
                Novosga\Model\ViewAtendimentoCodificado c
                JOIN c.servico s
                JOIN c.atendimento e
            WHERE
                e.unidade = :unidade AND
                e.dataChegada >= :dataInicial AND
                e.dataChegada <= :dataFinal
            GROUP BY
                s
            ORDER BY
                s.nome
        ");
        $query->setParameter('dataInicial', $dataInicial);
        $query->setParameter('dataFinal', $dataFinal);
        $query->setMaxResults(self::MAX_RESULTS);
        $dados = array();
        foreach ($unidades as $unidade) {
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
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\ViewAtendimento e
            WHERE
                e.unidade = :unidade AND
                e.status = :status AND
                e.dataChegada >= :dataInicial AND
                e.dataChegada <= :dataFinal
            ORDER BY
                e.dataChegada
        ");
        $query->setParameter('status', AtendimentoBusiness::ATENDIMENTO_ENCERRADO_CODIFICADO);
        $query->setParameter('dataInicial', $dataInicial);
        $query->setParameter('dataFinal', $dataFinal);
        $query->setMaxResults(self::MAX_RESULTS);
        foreach ($unidades as $unidade) {
            $query->setParameter('unidade', $unidade);
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
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\ViewAtendimento e
            WHERE
                e.unidade = :unidade AND
                e.dataChegada >= :dataInicial AND
                e.dataChegada <= :dataFinal
            ORDER BY
                e.dataChegada
        ");
        $query->setParameter('dataInicial', $dataInicial);
        $query->setParameter('dataFinal', $dataFinal);
        $query->setMaxResults(self::MAX_RESULTS);
        foreach ($unidades as $unidade) {
            $query->setParameter('unidade', $unidade);
            $dados[$unidade->getId()] = array(
                'unidade' => $unidade->getNome(),
                'atendimentos' => $query->getResult()
            );
        }
        return $dados;
    }
    
    private function tempo_medio_atendentes($dataInicial, $dataFinal) {
        $dados = array();
        $query = $this->em()->createQuery("
            SELECT
                CONCAT(u.nome, CONCAT(' ', u.sobrenome)) as atendente,
                COUNT(a) as total,
                AVG(a.dataChamada - a.dataChegada) as espera,
                AVG(a.dataInicio - a.dataChamada) as deslocamento,
                AVG(a.dataFim - a.dataInicio) as atendimento,
                AVG(a.dataFim - a.dataChegada) as tempoTotal
            FROM
                Novosga\Model\ViewAtendimento a
                JOIN a.usuario u
            WHERE
                a.dataChegada >= :dataInicial AND
                a.dataChegada <= :dataFinal AND
                a.dataFim IS NOT NULL
            GROUP BY
                u
            ORDER BY
                u.nome
        ");
        $query->setParameter('dataInicial', $dataInicial);
        $query->setParameter('dataFinal', $dataFinal);
        $query->setMaxResults(self::MAX_RESULTS);
        $dados = array();
        $rs = $query->getResult();
        foreach ($rs as $r) {
            $d = array(
                'atendente' => $r['atendente'],
                'total' => $r['total']
            );
            try {
                // se der erro tentando converter a data do banco para segundos, assume que ja esta em segundos
                // Isso é necessário para manter a compatibilidade entre os bancos
                $d['espera'] = DateUtil::timeToSec($r['espera']);
                $d['deslocamento'] = DateUtil::timeToSec($r['deslocamento']);
                $d['atendimento'] = DateUtil::timeToSec($r['atendimento']);
                $d['tempoTotal'] = DateUtil::timeToSec($r['tempoTotal']);
            } catch (\Exception $e) {
                $d['espera'] = $r['espera'];
                $d['deslocamento'] = $r['deslocamento'];
                $d['atendimento'] = $r['atendimento'];
                $d['tempoTotal'] = $r['tempoTotal'];
            }
            $dados[] = $d;
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
                Novosga\Model\Lotacao l
                LEFT JOIN l.usuario u
                LEFT JOIN l.grupo g
                LEFT JOIN l.cargo c
            WHERE
                g.left <= (
                    SELECT g2.left FROM Novosga\Model\Grupo g2 WHERE g2.id = (SELECT u2g.id FROM Novosga\Model\Unidade u2 INNER JOIN u2.grupo u2g WHERE u2.id = :unidade)
                ) AND
                g.right >= (
                    SELECT g3.right FROM Novosga\Model\Grupo g3 WHERE g3.id = (SELECT u3g.id FROM Novosga\Model\Unidade u3 INNER JOIN u3.grupo u3g WHERE u3.id = :unidade)
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
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Cargo e ORDER BY e.nome");
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
