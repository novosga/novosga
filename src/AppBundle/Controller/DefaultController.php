<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Modulo;
use AppBundle\Entity\Unidade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Novosga\Http\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/index.html.twig');
    }
    
    /**
     * @Route("/unidades", name="app_default_unidades")
     * @Method({"GET"})
     */
    public function unidadesAction(Request $request)
    {
        $unidades = $this->getDoctrine()->getManager()->getRepository(Unidade::class)->findAll();
        
        return $this->render('default/include/unidadesModal.html.twig', [
            'unidades' => $unidades,
        ]);
    }
    
    /**
     * @Route("/set_unidade", name="app_default_setunidade")
     * @Method({"POST"})
     */
    public function setUnidadeAction(Request $request)
    {
        $id = (int) $request->get('unidade');
        $request->getSession()->set('unidade', $id);
        
        return new JsonResponse(true);
    }
    
    /**
     * @Route("/menu", name="app_default_menu")
     * @Method({"GET"})
     */
    public function menuAction(Request $request)
    {
        $modulos = $this->getDoctrine()->getManager()->getRepository(Modulo::class)->findAll();
        
        return $this->render('default/include/menu.html.twig', [
            'modulos' => $modulos,
        ]);
    }
}
