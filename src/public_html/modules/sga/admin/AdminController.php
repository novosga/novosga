<?php
namespace modules\sga\admin;

use \novosga\SGA;
use \novosga\SGAContext;
use \novosga\http\AjaxResponse;
use \novosga\model\Configuracao;
use \novosga\model\util\Senha;
use \novosga\auth\Authentication;
use \novosga\controller\ModuleController;
use \novosga\business\PainelBusiness;
use \novosga\business\AtendimentoBusiness;
use \novosga\controller\CronController;
use \novosga\model\Modulo;

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
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        $paineis = array();
        foreach ($unidades as $unidade) {
            $paineis[$unidade->getId()] = PainelBusiness::paineis($unidade);
        }
        // método de autenticação
        $auth = Configuracao::get(Authentication::KEY);
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
            Configuracao::set(Authentication::KEY, $auth);
        }
        // tipo de numeração de senha
        $numeracao = Configuracao::get(Senha::TIPO_NUMERACAO);
        if ($numeracao) {
            $numeracao = $numeracao->getValor();
        } else {
            $numeracao = Senha::NUMERACAO_UNICA;
            Configuracao::set(Senha::TIPO_NUMERACAO, $numeracao);
        }
        $cron = new CronController($this->app());
        // view values
        $this->app()->view()->assign('unidades', $unidades);
        $this->app()->view()->assign('paineis', $paineis);
        $this->app()->view()->assign('auth', $auth);
        $this->app()->view()->assign('numeracao', $numeracao);
        $this->app()->view()->assign('numeracoes', $this->numeracoes);
        $this->app()->view()->assign('cronReiniciarSenhas', $cron->cronUrl('reiniciar_senhas', $context->getUser()));
    }
    
    public function auth_save(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $auth = Configuracao::get(Authentication::KEY);
            $value = $auth->getValor();
            $type = $context->request()->getParameter('type');
            $value['type'] = $type;
            if (!isset($value[$type])) {
                $value[$type] = array();
            }
            foreach ($_POST as $k => $v) {
                $value[$type][$k] = $v;
            }
            $auth = \novosga\auth\AuthFactory::create($value);
            if (!$auth) {
                throw new \Exception(_('Opção inválida'));
            }
            $auth->test();
            Configuracao::set(Authentication::KEY, $value);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function acumular_atendimentos(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            AtendimentoBusiness::acumularAtendimentos();
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
            Configuracao::set(Senha::TIPO_NUMERACAO, $tipo);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function painel_info(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $unidade = (int) $context->request()->getParameter('unidade');
            $host = (int) $context->request()->getParameter('host');
            $response->data = PainelBusiness::painelInfo($unidade, $host);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }

}
