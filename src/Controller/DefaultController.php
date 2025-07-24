<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Repository\UnidadeRepository;
use App\Entity\Unidade;
use App\Service\UsuarioService;
use Novosga\Entity\UsuarioInterface;
use Novosga\Http\Envelope;
use Novosga\Service\ModuleServiceInterface;
use Novosga\Service\UsuarioServiceInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(ModuleServiceInterface $service): Response
    {
        $modules = $service->getInstalledModules();

        return $this->render('default/index.html.twig', [
            'modules' => $modules,
        ]);
    }

    #[Route('/ping', name: 'ping', methods: ['GET'])]
    public function ping(): Response
    {
        return $this->json(new Envelope());
    }

    #[Route('/about', name: 'about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('default/about.html.twig');
    }

    #[Route('/unidades', name: 'app_default_unidades', methods: ['GET'])]
    public function unidades(UnidadeRepository $unidade): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $unidades = $unidade->findByUsuario($usuario);

        return $this->render('default/include/unidadesModal.html.twig', [
            'unidades' => $unidades,
        ]);
    }

    #[Route('/set_unidade/{id}', name: 'app_default_setunidade', methods: ['POST'])]
    public function setUnidade(Unidade $unidade, UsuarioService $usuarioService): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $usuarioService->meta(
            $usuario,
            UsuarioServiceInterface::ATTR_SESSION_UNIDADE,
            $unidade->getId(),
        );

        return $this->json(new Envelope());
    }

    #[Route('/menu', name: 'app_default_menu', methods: ['GET'])]
    public function menu(ModuleServiceInterface $service): Response
    {
        $modules = $service->getInstalledModules();

        return $this->render('default/include/menu.html.twig', [
            'modules' => $modules,
        ]);
    }
}
