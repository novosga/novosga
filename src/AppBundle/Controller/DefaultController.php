<?php

namespace AppBundle\Controller;

use Novosga\Entity\Unidade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Novosga\Http\Envelope;

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
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('default/about.html.twig');
    }

    /**
     * @Route("/unidades", name="app_default_unidades")
     * @Method({"GET"})
     */
    public function unidadesAction(Request $request)
    {
        $usuario = $this->getUser();
        $unidades = $this->getDoctrine()->getManager()->getRepository(Unidade::class)->findByUsuario($usuario);

        return $this->render('default/include/unidadesModal.html.twig', [
            'unidades' => $unidades,
        ]);
    }

    /**
     * @Route("/set_unidade/{id}", name="app_default_setunidade")
     * @Method({"POST"})
     */
    public function setUnidadeAction(Request $request, Unidade $unidade)
    {
        $listener = $this->get('novosga.security.listener');
        $listener->updateUnidade($request, $this->getUser(), $unidade);

        return $this->json(new Envelope());
    }

    /**
     * @Route("/menu", name="app_default_menu")
     * @Method({"GET"})
     */
    public function menuAction(Request $request)
    {
        $kernel = $this->get('kernel');
        $bundles = [];
        
        if ($kernel instanceof \Symfony\Component\HttpKernel\Kernel) {
            $bundles = array_filter($kernel->getBundles(), function ($bundle) {
                return $bundle instanceof \Novosga\Module\ModuleInterface;
            });
        }
        
        return $this->render('default/include/menu.html.twig', [
            'modules' => $bundles,
        ]);
    }
}
