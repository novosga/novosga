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

use App\Entity\NovaSenha;
use App\Service\TicketService;
use Novosga\Entity\Atendimento;
use Novosga\Service\AtendimentoService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * TriagemController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 * @Route("/api")
 */
class TriagemController extends ApiControllerBase
{
    /**
     * @Route("/print/{id}", methods={"GET"})
     */
    public function imprimir(
        Request $request,
        Atendimento $atendimento,
        TranslatorInterface $translator,
        TicketService $service
    ) {
        $hash = $request->headers->get('X-HASH') ?? $request->get('hash');
        
        if ($hash !== $atendimento->hash()) {
            $error = $translator->trans('api.triage.invalid_hash');
            throw new Exception($error);
        }

        $html = $service->printTicket($atendimento);

        return new Response($html);
    }
    
    /**
     * @Route("/distribui", methods={"POST"})
     */
    public function distribui(Request $request, AtendimentoService $service, LoggerInterface $logger)
    {
        try {
            $json = $request->getContent();
            
            $logger->info('[/api/distribui] ' . $json);
        
            $novaSenha = $this
                ->getSerializer()
                ->deserialize($json, NovaSenha::class, 'json');
            
            $usuario    = $this->getUser()->getId();
            $unidade    = (int) $novaSenha->unidade;
            $servico    = (int) $novaSenha->servico;
            $prioridade = (int) $novaSenha->prioridade;
            $cliente    = $novaSenha->cliente;
            
            $response = $service->distribuiSenha($unidade, $usuario, $servico, $prioridade, $cliente);
        } catch (Exception $ex) {
            $response = [
                'error' => $ex->getMessage()
            ];
            
            $logger->error('[/api/distribui] ' . $ex->getMessage());
        }
        
        return $this->json($response);
    }
}
