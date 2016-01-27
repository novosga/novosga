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
 * AdminLocaisController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/locais")
 */
class AdminLocaisController extends Controller
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
     * @Route("/", name="admin_locais_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        return $this->render('admin/locais.html.twig', [
        ]);
    }

}
