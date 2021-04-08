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

use App\Repository\ORM\UnidadeRepository;
use App\Repository\ORM\UsuarioRepository;
use Novosga\Entity\Unidade;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Novosga\Http\Envelope;

use function usort;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/ping", name="ping")
     */
    public function ping(Request $request)
    {
        return $this->json(new Envelope());
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
    public function unidades(Request $request, UnidadeRepository $unidade)
    {
        $usuario = $this->getUser();
        $unidades = $unidade->findByUsuario($usuario);

        return $this->render('default/include/unidadesModal.html.twig', [
            'unidades' => $unidades,
        ]);
    }

    /**
     * @Route("/set_unidade/{id}", name="app_default_setunidade", methods={"POST"})
     */
    public function setUnidade(Request $request, Unidade $unidade, UsuarioRepository $repository)
    {
        $usuario = $this->getUser();
        $repository->updateUnidade($usuario, $unidade);
        
        return $this->json(new Envelope());
    }

    /**
     * @Route("/menu", name="app_default_menu", methods={"GET"})
     */
    public function menu(Request $request, KernelInterface $kernel, TranslatorInterface $translator)
    {
        $modules = [];
        
        foreach ($kernel->getBundles() as $bundle) {
            if ($bundle instanceof \Novosga\Module\ModuleInterface) {
                $displayName = $translator->trans(
                    $bundle->getDisplayName(),
                    [],
                    $bundle->getName()
                );

                $modules[] = [
                    'keyName'     => $bundle->getKeyName(),
                    'roleName'    => $bundle->getRoleName(),
                    'iconName'    => $bundle->getIconName(),
                    'displayName' => $displayName,
                    'name'        => $bundle->getName(),
                    'homeRoute'   => $bundle->getHomeRoute(),
                ];
            }
        }

        usort($modules, function ($a, $b) {
            return strcasecmp($a['displayName'], $b['displayName']);
        });
        
        return $this->render('default/include/menu.html.twig', [
            'modules' => $modules,
        ]);
    }
}
