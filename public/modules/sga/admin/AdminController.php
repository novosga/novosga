<?php
namespace modules\sga\admin;

use \Novosga\SGA;
use \Novosga\SGAContext;
use \Novosga\Http\AjaxResponse;
use \Novosga\Model\Configuracao;
use \Novosga\Model\Util\Senha;
use \Novosga\Auth\Authentication;
use \Novosga\Controller\ModuleController;
use \Novosga\Business\AtendimentoBusiness;
use \Novosga\Controller\CronController;
use \Novosga\Model\Modulo;

/**
 * AdminView
 * @author rogeriolino
 */
class AdminController extends ModuleController {
    
    private $numeracoes;
    
    public function __construct(SGA $app, Modulo $modulo) {
        parent::__construct($app, $modulo);
        $this->numeracoes = array(Senha::NUMERACAO_UNICA => _('Incremental única'), Senha::NUMERACAO_SERVICO => _('Incremental por serviço'));
    }
    
    public function index(SGAContext $context) {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        // método de autenticação
        $auth = Configuracao::get($this->em(), Authentication::KEY);
        if ($auth) {
            $auth = $auth->getValor();
        } else {
            $auth = array(
                'type' => 'db',
                'db' => array(),
                'ldap' => array(
                    'host' => '',
                    'port' => '',
                    'baseDn' => '',
                    'loginAttribute' => '',
                    'username' => '',
                    'password' => '',
                    'filter' => '',
                )
            );
            Configuracao::set($this->em(), Authentication::KEY, $auth);
        }
        // tipo de numeração de senha
        $numeracao = Configuracao::get($this->em(), Senha::TIPO_NUMERACAO);
        if ($numeracao) {
            $numeracao = $numeracao->getValor();
        } else {
            $numeracao = Senha::NUMERACAO_UNICA;
            Configuracao::set($this->em(), Senha::TIPO_NUMERACAO, $numeracao);
        }
        $cron = new CronController($this->app());
        // view values
        $this->app()->view()->assign('unidades', $unidades);
        $this->app()->view()->assign('auth', $auth);
        $this->app()->view()->assign('numeracao', $numeracao);
        $this->app()->view()->assign('numeracoes', $this->numeracoes);
        $this->app()->view()->assign('cronReiniciarSenhas', $cron->cronUrl('reset', $context->getUser()));
    }
    
    public function auth_save(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $auth = Configuracao::get($this->em(), Authentication::KEY);
            $value = $auth->getValor();
            $type = $context->request()->getParameter('type');
            $value['type'] = $type;
            if (!isset($value[$type])) {
                $value[$type] = array();
            }
            foreach ($_POST as $k => $v) {
                $value[$type][$k] = $v;
            }
            $auth = \Novosga\Auth\AuthFactory::create($value);
            if (!$auth) {
                throw new \Exception(_('Opção inválida'));
            }
            $auth->test();
            Configuracao::set($this->em(), Authentication::KEY, $value);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function acumular_atendimentos(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $ab = new AtendimentoBusiness($this->em());
            $ab->acumularAtendimentos();
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function change_numeracao(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $tipo = (int) $context->request()->getParameter('tipo');
            if (!array_key_exists($tipo, $this->numeracoes)) {
                throw new \Exception(_('Valor inválido'));
            }
            Configuracao::set($this->em(), Senha::TIPO_NUMERACAO, $tipo);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }

}
