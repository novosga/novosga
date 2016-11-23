<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\NovaSenha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Novosga\Service\AtendimentoService;

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
     * @Route("/distribui")
     * @Method("POST")
     */
    public function distribuiAction(Request $request)
    {
        try {
            $json = $request->getContent();
            $manager = $this->getManager();
        
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
        }
        
        return $this->json($response);
    }
}
