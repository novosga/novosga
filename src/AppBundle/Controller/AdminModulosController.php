<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * AdminModulosController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/modulos")
 */
class AdminModulosController extends Controller
{

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="admin_modulos_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/modulos.html.twig', [
            'tab' => 'modulos',
        ]);
    }

}
