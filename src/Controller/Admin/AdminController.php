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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AdminController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route("/admin")]
class AdminController extends AbstractController
{
    #[Route("/", name: "admin_index")]
    public function index(Request $request): Response
    {
        return $this->render('admin/index.html.twig', [
            'tab' => 'index',
        ]);
    }

    #[Route("/acumular_atendimentos", name: "admin_acumular_atendimentos", methods: ["POST"])]
    public function acumularAtendimentos(Request $request, AtendimentoService $service): Response
    {
        $envelope = new Envelope();
        $service->acumularAtendimentos(null);

        return $this->json($envelope);
    }

    #[Route("/limpar_atendimentos", name: "admin_limpar_atendimentos", methods: ["POST"])]
    public function limparAtendimentos(Request $request, AtendimentoService $service): Response
    {
        $envelope = new Envelope();
        $service->limparDados();

        return $this->json($envelope);
    }
}
