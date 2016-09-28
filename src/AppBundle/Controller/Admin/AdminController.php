<?php

namespace AppBundle\Controller\Admin;

use Novosga\App;
use Novosga\Auth\AuthenticationProvider;
use Novosga\Context;
use Novosga\Http\Envelope;
use Novosga\Entity\Configuracao;
use Novosga\Entity\Senha;
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
        return $this->render('admin/index.html.twig', [
            'tab' => 'index',
        ]);
    }

    public function auth_save(Context $context)
    {
        $envelope = new Envelope();
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
        } catch (\Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    public function acumular_atendimentos(Context $context)
    {
        $envelope = new Envelope();
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $service = new AtendimentoService($em);
            $service->acumularAtendimentos();
        } catch (\Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    public function change_numeracao(Context $context)
    {
        $envelope = new Envelope();
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $tipo = (int) $context->request()->post('tipo');
            if (!array_key_exists($tipo, $this->numeracoes)) {
                throw new \Exception(_('Valor inválido'));
            }
            Configuracao::set($em, Senha::TIPO_NUMERACAO, $tipo);
        } catch (\Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

}
