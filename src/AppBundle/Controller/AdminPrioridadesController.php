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
 * AdminPrioridadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/prioridades")
 */
class AdminPrioridadesController extends Controller
{
    private $numeracoes;

    public function __construct()
    {
    }

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="admin_prioridades_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        return $this->render('admin/prioridades.html.twig', [
        ]);
    }

}
