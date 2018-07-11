<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use Novosga\Entity\Unidade;
use Novosga\Service\AtendimentoService;
use Novosga\Service\FilaService;
use Novosga\Service\UsuarioService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * FilasController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * @Route("/api/filas")
 */
class FilasController extends Controller
{
    /**
     * Retorna a lista de atendimentos do usuário atual na unidade informada.
     *
     * @Route("/{unidadeId}", methods={"GET"})
     * @ParamConverter("unidade", class="Novosga\Entity\Unidade", options={"id" = "unidadeId"})
     */
    public function atendimentosUsuario(
        FilaService $filaService,
        UsuarioService $usuarioService,
        Unidade $unidade
    ) {
        $usuario      = $this->getUser();
        $servicos     = $usuarioService->servicos($usuario, $unidade);
        $atendimentos = $filaService->filaAtendimento($unidade, $servicos);
        
        return $this->json($atendimentos);
    }
    
    /**
     * Atualiza o statuso do atendimento atual do usuário para o novo status
     * informado.
     *
     * @Route("", methods={"PUT"})
     */
    public function alteraStatus(AtendimentoService $atendimentoService, Request $request)
    {
        $novoStatus  = $request->get('novoStatus');
        $usuario     = $this->getUser();
        $atendimento = $atendimentoService->alteraStatusAtendimentoUsuario($usuario, $novoStatus);
        
        return $this->json($atendimento);
    }
}
