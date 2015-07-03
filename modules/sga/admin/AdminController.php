<?php

namespace modules\sga\admin;

use Novosga\App;
use Novosga\Context;
use Novosga\Http\JsonResponse;
use Novosga\Model\Configuracao;
use Novosga\Model\Util\Senha;
use Novosga\Auth\AuthenticationProvider;
use Novosga\Controller\ModuleController;
use Novosga\Service\AtendimentoService;
use Novosga\Model\Modulo;

/**
 * AdminView.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AdminController extends ModuleController
{
    private $numeracoes;

    public function __construct(App $app, Modulo $modulo)
    {
        parent::__construct($app, $modulo);
        $this->numeracoes = array(Senha::NUMERACAO_UNICA => _('Incremental única'), Senha::NUMERACAO_SERVICO => _('Incremental por serviço'));
    }

    public function index(Context $context)
    {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        // método de autenticação
        $auth = Configuracao::get($this->em(), AuthenticationProvider::KEY);
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
                ),
            );
            Configuracao::set($this->em(), AuthenticationProvider::KEY, $auth);
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
        $this->app()->view()->set('dbValues', array_filter($context->database()->values(), function ($item) {
            return is_string($item);
        }));
        // authentication config
        $this->app()->view()->set('auth', $auth);
        $this->app()->view()->set('numeracao', $numeracao);
        $this->app()->view()->set('numeracoes', $this->numeracoes);
    }

    public function auth_save(Context $context)
    {
        $response = new JsonResponse();
        try {
            $auth = Configuracao::get($this->em(), AuthenticationProvider::KEY);
            $value = $auth->getValor();
            $type = $context->request()->post('type');
            $value['type'] = $type;
            if (!isset($value[$type])) {
                $value[$type] = array();
            }
            foreach ($_POST as $k => $v) {
                $value[$type][$k] = $v;
            }
            $auth = App::authenticationFactory()->create($context, $value);
            if (!$auth) {
                throw new \Exception(_('Opção inválida'));
            }
            $auth->test();
            Configuracao::set($this->em(), AuthenticationProvider::KEY, $value);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function acumular_atendimentos(Context $context)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $service = new AtendimentoService($this->em());
            $service->acumularAtendimentos();
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function change_numeracao(Context $context)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $tipo = (int) $context->request()->post('tipo');
            if (!array_key_exists($tipo, $this->numeracoes)) {
                throw new \Exception(_('Valor inválido'));
            }
            Configuracao::set($this->em(), Senha::TIPO_NUMERACAO, $tipo);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function add_oauth_client(Context $context)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $client_id = $context->request()->post('client_id');
            $client_secret = $context->request()->post('client_secret');
            $redirect_uri = $context->request()->post('redirect_uri');
            // apaga se ja existir
            $this->delete_auth_client_by_id($client_id);
            // insere novo cliente
            $client = new \Novosga\Model\OAuthClient();
            $client->setId($client_id);
            $client->setSecret($client_secret);
            $client->setRedirectUri($redirect_uri);

            $this->em()->persist($client);
            $this->em()->flush();

            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function get_oauth_client(Context $context)
    {
        $response = new JsonResponse(true);
        $client_id = $context->request()->get('client_id');
        $query = $this->em()->createQuery('SELECT e FROM Novosga\Model\OAuthClient e WHERE e.id = :client_id');
        $query->setParameter('client_id', $client_id);
        $client = $query->getOneOrNullResult();
        if ($client) {
            $response->data = $client->jsonSerialize();
        }

        return $response;
    }

    public function get_all_oauth_client(Context $context)
    {
        $response = new JsonResponse(true);
        $rs = $this->em()->getRepository('Novosga\Model\OAuthClient')->findBy(array(), array('id' => 'ASC'));
        $response->data = array();
        foreach ($rs as $client) {
            $response->data[] = $client->jsonSerialize();
        }

        return $response;
    }

    public function delete_oauth_client(Context $context)
    {
        $response = new JsonResponse(true);
        $client_id = $context->request()->post('client_id');
        $this->delete_auth_client_by_id($client_id);

        return $response;
    }

    private function delete_auth_client_by_id($client_id)
    {
        $query = $this->em()->createQuery('DELETE Novosga\Model\OAuthClient e WHERE e.id = :client_id');
        $query->setParameter('client_id', $client_id);
        $query->execute();
    }
}
