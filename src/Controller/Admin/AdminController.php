<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use Novosga\Http\Envelope;
use App\Service\AtendimentoService;
use Novosga\Entity\UsuarioInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AdminController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'tab' => 'index',
        ]);
    }

    #[Route('/acumular_atendimentos', name: 'acumular_atendimentos', methods: ['POST'])]
    public function acumularAtendimentos(AtendimentoService $service, ClockInterface $clock): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();

        $envelope = new Envelope();
        $service->acumularAtendimentos($usuario, null, $clock->now());

        return $this->json($envelope);
    }

    #[Route('/limpar_atendimentos', name: 'limpar_atendimentos', methods: ['POST'])]
    public function limparAtendimentos(AtendimentoService $service): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();

        $envelope = new Envelope();
        $service->limparDados($usuario, null);

        return $this->json($envelope);
    }
}
