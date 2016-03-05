<?php

namespace AppBundle\Controller;

use Novosga\App;
use Novosga\Auth\AuthenticationProvider;
use Novosga\Context;
use Novosga\Http\JsonResponse;
use Novosga\Entity\Configuracao;
use Novosga\Entity\Util\Senha;
use Novosga\Service\AtendimentoService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * AdminController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin")
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
     * @Route("/", name="admin_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('admin/index.html.twig', [
            'tab' => 'index',
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

}
