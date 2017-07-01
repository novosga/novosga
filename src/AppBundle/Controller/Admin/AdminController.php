<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/acumular_atendimentos", name="admin_acumular_atendimentos")
     * @Method("POST")
     */
    public function acumular_atendimentos(Request $request)
    {
        $envelope = new Envelope();
        try {
            $em = $this->getDoctrine()->getManager();
            $service = new AtendimentoService($em);
            $service->acumularAtendimentos();
        } catch (\Exception $e) {
            $envelope->exception($e);
        }

        return $this->json($envelope);
    }
}
