<?php
namespace modules\sga\admin;

use \Novosga\SGA;
use \Novosga\Context;
use \Novosga\Http\AjaxResponse;
use \Novosga\Model\Configuracao;
use \Novosga\Model\Util\Senha;
use \Novosga\Auth\Authentication;
use \Novosga\Controller\ModuleController;
use \Novosga\Business\AtendimentoBusiness;
use \Novosga\Model\Modulo;

/**
 * AdminView
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AdminController extends ModuleController {
    
    private $numeracoes;
    
    public function __construct(SGA $app, Modulo $modulo) {
        parent::__construct($app, $modulo);
        $this->numeracoes = array(Senha::NUMERACAO_UNICA => _('Incremental única'), Senha::NUMERACAO_SERVICO => _('Incremental por serviço'));
    }
    
    public function index(Context $context) {
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
        // view values
        $this->app()->view()->set('unidades', $unidades);
        // database config
        $this->app()->view()->set('dbValues', $context->database()->values());
        // authentication config
        $this->app()->view()->set('auth', $auth);
        $this->app()->view()->set('numeracao', $numeracao);
        $this->app()->view()->set('numeracoes', $this->numeracoes);
    }
    
    public function auth_save(Context $context) {
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
            $auth = \Novosga\Auth\AuthFactory::create($context, $type, $value);
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
    
    public function acumular_atendimentos(Context $context) {
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
    
    public function change_numeracao(Context $context) {
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
    
    public function add_oauth_client(Context $context) {
        $response = new AjaxResponse();
        if ($context->request()->isPost()) {
            try {
                $client_id = $context->request()->getParameter('client_id');
                $client_secret = $context->request()->getParameter('client_secret');
                $redirect_uri = $context->request()->getParameter('redirect_uri');
                // apaga se ja existir
                $this->delete_auth_client_by_id($client_id);
                // insere novo cliente
                $conn = $this->em()->getConnection();
                $stmt = $conn->prepare('INSERT INTO oauth_clients (client_id, client_secret, redirect_uri) VALUES (:client_id, :client_secret, :redirect_uri)');
                $stmt->bindValue('client_id', $client_id);
                $stmt->bindValue('client_secret', $client_secret);
                $stmt->bindValue('redirect_uri', $redirect_uri);
                $stmt->execute();
                $response->success = true;
            } catch (\Exception $e) {
                $response->message = $e->getMessage();
            }
        }
        $context->response()->jsonResponse($response);
    }
    
    public function get_oauth_client(Context $context) {
        $response = new AjaxResponse(true);
        $client_id = $context->request()->getParameter('client_id');
        $conn = $this->em()->getConnection();
        $stmt = $conn->prepare('SELECT client_id, client_secret, redirect_uri FROM oauth_clients WHERE client_id = :client_id');
        $stmt->bindValue('client_id', $client_id);
        $stmt->execute();
        $response->data = $stmt->fetch();
        $context->response()->jsonResponse($response);
    }
    
    public function get_all_oauth_client(Context $context) {
        $response = new AjaxResponse(true);
        $conn = $this->em()->getConnection();
        $stmt = $conn->prepare('SELECT client_id, client_secret, redirect_uri FROM oauth_clients ORDER BY client_id');
        $stmt->execute();
        $response->data = $stmt->fetchAll();
        $context->response()->jsonResponse($response);
    }
    
    public function delete_oauth_client(Context $context) {
        $response = new AjaxResponse(true);
        $conn = $this->em()->getConnection();
        $client_id = $context->request()->getParameter('client_id');
        $this->delete_auth_client_by_id($client_id);
        $context->response()->jsonResponse($response);
    }
    
    private function delete_auth_client_by_id($client_id) {
        $conn = $this->em()->getConnection();
        $stmt = $conn->prepare('DELETE FROM oauth_clients WHERE client_id = :client_id');
        $stmt->bindValue('client_id', $client_id);
        $stmt->execute();
    }
    
}
