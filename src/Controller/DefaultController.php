<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Novosga\Http\Envelope;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(Request $request)
    {
        return $this->render('default/about.html.twig');
    }

    /**
     * @Route("/unidades", name="app_default_unidades", methods={"GET"})
     */
    public function unidades(Request $request)
    {
        $usuario  = $this->getUser();
        $unidades = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Unidade::class)
            ->findByUsuario($usuario);

        return $this->render('default/include/unidadesModal.html.twig', [
            'unidades' => $unidades,
        ]);
    }

    /**
     * @Route("/set_unidade/{id}", name="app_default_setunidade", methods={"POST"})
     */
    public function setUnidade(Request $request, Unidade $unidade)
    {
        $usuario = $this->getUser();
        
        $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Usuario::class)
            ->updateUnidade($usuario, $unidade);
        
        return $this->json(new Envelope());
    }

    /**
     * @Route("/menu", name="app_default_menu", methods={"GET"})
     */
    public function menu(KernelInterface $kernel, Request $request)
    {
        $bundles = array_filter($kernel->getBundles(), function ($bundle) {
            return $bundle instanceof \Novosga\Module\ModuleInterface;
        });

        usort($bundles, function (\Novosga\Module\ModuleInterface $a, \Novosga\Module\ModuleInterface $b) {
            return strcasecmp($a->getDisplayName(), $b->getDisplayName());
        });
        
        return $this->render('default/include/menu.html.twig', [
            'modules' => $bundles,
        ]);
    }
}
