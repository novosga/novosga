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

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * AgendamentosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 * @Route("/api/agendamentos")
 */
class AgendamentosController extends ApiCrudController
{
    use Actions\GetTrait,
        Actions\FindTrait;
    
    public function getEntityName()
    {
        return \Novosga\Entity\Agendamento::class;
    }
    
    /**
     * @Route("", methods={"POST"})
     */
    public function post(
        Request $request,
        \Novosga\Service\AtendimentoService $atendimentoService
    ) {
        $json = $request->getContent();
        $object = json_decode($json, true);
        $agendamento = new \Novosga\Entity\Agendamento();
        
        $form = $this->createForm(\App\Form\Api\AgendamentoType::class, $agendamento, [

        ]);
        $form->submit($object);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $message = 'Formulário inválido';
            
            foreach ($form->getErrors(true) as $error) {
                $message . ': ' . $error->getMessage();
                break;
            }
            
            throw new \Exception($message);
        }
        
        $clienteValido = $atendimentoService->getClienteValido($agendamento->getCliente());
        
        if (!$clienteValido) {
            throw new \Exception('Favor preencher os dados do cliente.');
        }
        
        $agendamento->setCliente($clienteValido);
        
        $atendimentoService->checkServicoUnidade($agendamento->getUnidade(), $agendamento->getServico());
        
        $om = $this->getDoctrine()->getManager();
        $om->persist($agendamento);
        $om->flush();
        
        return $this->json($agendamento);
    }
}
