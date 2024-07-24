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

namespace App\Controller\Api;

use App\Service\AtendimentoService;
use App\Service\FilaService;
use App\Service\UnidadeService;
use App\Service\UsuarioService;
use Novosga\Entity\UsuarioInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FilasController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
#[Route('/api/filas')]
class FilasController extends AbstractController
{
    /**
     * Retorna a lista de atendimentos do usuário atual na unidade informada.
     */
    #[Route('/{unidadeId}', methods: ['GET'])]
    public function atendimentosUsuario(
        FilaService $filaService,
        UsuarioService $usuarioService,
        UnidadeService $unidadeService,
        int $unidadeId,
    ): Response {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $unidade = $unidadeService->getById($unidadeId);
        $servicos = $usuarioService->getServicosUnidade($usuario, $unidade);
        $atendimentos = $filaService->getFilaAtendimento($unidade, $usuario, $servicos);

        return $this->json($atendimentos);
    }

    /**
     * Atualiza o statuso do atendimento atual do usuário para o novo status
     * informado.
     */
    #[Route('', methods: ['PUT'])]
    public function alteraStatus(Request $request, AtendimentoService $atendimentoService): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $novoStatus = $request->get('novoStatus', '');
        $atendimento = $atendimentoService->alteraStatusAtendimentoUsuario($usuario, $novoStatus);

        return $this->json($atendimento);
    }
}
