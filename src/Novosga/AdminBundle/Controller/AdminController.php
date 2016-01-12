<?php

namespace Novosga\AdminBundle\Controller;

use Novosga\App;
use Novosga\Auth\AuthenticationProvider;
use Novosga\Context;
use Novosga\Http\JsonResponse;
use AppBundle\Entity\Configuracao;
use AppBundle\Entity\Util\Senha;
use Novosga\Service\AtendimentoService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * AdminView.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 * 
 */
class AdminController extends Controller
{
    private $numeracoes;

    public function __construct()
    {
        $this->numeracoes = [
            Senha::NUMERACAO_UNICA => _('Incremental única'), 
            Senha::NUMERACAO_SERVICO => _('Incremental por serviço')
        ];
    }

    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery("SELECT e FROM AppBundle\Entity\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        // método de autenticação
        $auth = Configuracao::get($em, AuthenticationProvider::KEY);
        if ($auth) {
            $auth = $auth->getValor();
        } else {
            $auth = [
                'type' => 'db',
                'db'   => [],
                'ldap' => [
                    'host'           => '',
                    'port'           => '',
                    'baseDn'         => '',
                    'loginAttribute' => '',
                    'username'       => '',
                    'password'       => '',
                    'filter'         => '',
                ],
            ];
            Configuracao::set($em, AuthenticationProvider::KEY, $auth);
        }
        // tipo de numeração de senha
        $numeracao = Configuracao::get($em, Senha::TIPO_NUMERACAO);
        if ($numeracao) {
            $numeracao = $numeracao->getValor();
        } else {
            $numeracao = Senha::NUMERACAO_UNICA;
            Configuracao::set($em, Senha::TIPO_NUMERACAO, $numeracao);
        }
        
        return $this->render('NovosgaAdminBundle:Admin:index.html.twig', [
            'unidade' => $em->find(\AppBundle\Entity\Unidade::class, 1),
            'modulos' => [],
            'unidades' => $unidades,
            // authentication config
            'auth' => $auth,
            'numeracao' => $numeracao,
            'numeracoes' => $this->numeracoes,
            // database config
//            'dbValues', array_filter($context->database()->values(), function ($item) {
//                return is_string($item);
//            })
            'dbValues' => array_filter([], function ($item) {
                return is_string($item);
            })
        ]);
    }

    public function auth_save(Context $context)
    {
        $response = new JsonResponse();
        try {
            $auth = Configuracao::get($em, AuthenticationProvider::KEY);
            $value = $auth->getValor();
            $type = $context->request()->post('type');
            $value['type'] = $type;
            if (!isset($value[$type])) {
                $value[$type] = [];
            }
            foreach ($_POST as $k => $v) {
                $value[$type][$k] = $v;
            }
            $auth = App::authenticationFactory()->create($context, $value);
            if (!$auth) {
                throw new \Exception(_('Opção inválida'));
            }
            $auth->test();
            Configuracao::set($em, AuthenticationProvider::KEY, $value);
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
            $service = new AtendimentoService($em);
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
            Configuracao::set($em, Senha::TIPO_NUMERACAO, $tipo);
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
            $client = new \AppBundle\Entity\OAuthClient();
            $client->setId($client_id);
            $client->setSecret($client_secret);
            $client->setRedirectUri($redirect_uri);

            $em->persist($client);
            $em->flush();

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
        $query = $em->createQuery('SELECT e FROM AppBundle\Entity\OAuthClient e WHERE e.id = :client_id');
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
        $rs = $em->getRepository('AppBundle\Entity\OAuthClient')->findBy([], ['id' => 'ASC']);
        $response->data = [];
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
        $query = $em->createQuery('DELETE AppBundle\Entity\OAuthClient e WHERE e.id = :client_id');
        $query->setParameter('client_id', $client_id);
        $query->execute();
    }
}
