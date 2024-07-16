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

use App\Entity\Unidade;
use App\Service\AtendimentoService;
use App\Service\FilaService;
use App\Service\UsuarioService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * FilasController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
#[Route("/api/filas")]
class FilasController extends AbstractController
{
    /**
     * Retorna a lista de atendimentos do usuário atual na unidade informada.
     */
    #[Route("/{unidadeId}", methods: ["GET"])]
    public function atendimentosUsuario(
        FilaService $filaService,
        UsuarioService $usuarioService,
        #[MapEntity(mapping: ['unidadeId' => 'id'])] Unidade $unidade,
    ) {
        $usuario      = $this->getUser();
        $servicos     = $usuarioService->servicos($usuario, $unidade);
        $atendimentos = $filaService->filaAtendimento($unidade, $usuario, $servicos);

        return $this->json($atendimentos);
    }

    /**
     * Atualiza o statuso do atendimento atual do usuário para o novo status
     * informado.
     */
    #[Route("", methods: ["PUT"])]
    public function alteraStatus(AtendimentoService $atendimentoService, Request $request)
    {
        $novoStatus  = $request->get('novoStatus');
        $usuario     = $this->getUser();
        $atendimento = $atendimentoService->alteraStatusAtendimentoUsuario($usuario, $novoStatus);

        return $this->json($atendimento);
    }
}
