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

use App\Dto\NovaSenha;
use App\Service\TicketService;
use App\Entity\Atendimento;
use App\Service\AtendimentoService;
use Exception;
use Novosga\Entity\UsuarioInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TriagemController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
#[Route('/api')]
class TriagemController extends ApiControllerBase
{
    #[Route('/print/{id}', methods: ['GET'])]
    public function imprimir(
        Request $request,
        Atendimento $atendimento,
        TranslatorInterface $translator,
        TicketService $service,
    ): Response {
        $hash = $request->headers->get('X-HASH') ?? $request->get('hash');

        if ($hash !== $atendimento->hash()) {
            $error = $translator->trans('api.triage.invalid_hash');
            throw new Exception($error);
        }

        $html = $service->printTicket($atendimento);

        return new Response($html);
    }

    #[Route('/distribui', methods: ['POST'])]
    public function distribui(
        Request $request,
        #[MapRequestPayload] NovaSenha $novaSenha,
        AtendimentoService $service,
        LoggerInterface $logger
    ): Response {
        try {
            $json = $request->getContent();

            $logger->info('[/api/distribui] ' . $json);

            /** @var UsuarioInterface */
            $usuario = $this->getUser();
            $unidade = (int) $novaSenha->unidade;
            $servico = (int) $novaSenha->servico;
            $prioridade = (int) $novaSenha->prioridade;
            $cliente = $novaSenha->cliente;

            $response = $service->distribuiSenha($unidade, $usuario, $servico, $prioridade, $cliente);
            $status = 201;
        } catch (Exception $ex) {
            $response = [
                'error' => $ex->getMessage(),
            ];
            $status = 422;

            $logger->error('[/api/distribui] ' . $ex->getMessage());
        }

        return $this->json($response, $status);
    }
}
