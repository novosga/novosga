<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Controller;

use ApiBundle\Entity\NovaSenha;
use Novosga\Entity\Atendimento;
use Novosga\Service\AtendimentoService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * TriagemController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class TriagemController extends ApiControllerBase
{
        
    public function __construct()
    {
    }
    
    /**
     * @Route("/print/{id}/{hash}")
     * @Method("POST")
     */
    public function imprimirAction(Request $request, Atendimento $atendimento, $hash)
    {
        if ($hash !== $atendimento->hash()) {
            throw new Exception(_('Chave de segurança do atendimento inválida'));
        }

        return $this->printTicket($atendimento);
    }
    
    /**
     * @Route("/distribui")
     * @Method("POST")
     */
    public function distribuiAction(Request $request)
    {
        $logger = $this->get('logger');
        
        try {
            $json = $request->getContent();
            $manager = $this->getManager();
            
            $logger->info('[/api/distribui] ' . $json);
        
            $serializer = $this->getSerializer();
            $novaSenha = $serializer->deserialize($json, NovaSenha::class, 'json');

            $service = new AtendimentoService($manager);

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
            
            $logger->error($ex->getMessage());
        }
        
        return $this->json($response);
    }
}
