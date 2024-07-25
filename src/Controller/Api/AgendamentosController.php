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

use App\Entity\Agendamento;
use App\Form\Api\AgendamentoType;
use App\Service\AtendimentoService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ApiCrudController<Agendamento>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
#[Route('/api/agendamentos')]
class AgendamentosController extends ApiCrudController
{
    use Actions\GetTrait;
    use Actions\FindTrait;

    public function getEntityName(): string
    {
        return Agendamento::class;
    }

    #[Route('', methods: ['POST'])]
    public function post(
        Request $request,
        AtendimentoService $atendimentoService,
    ): Response {
        $json = $request->getContent();
        $object = json_decode($json, true);
        $agendamento = new \App\Entity\Agendamento();

        $form = $this
            ->createForm(AgendamentoType::class, $agendamento)
            ->submit($object);

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

        $em = $this->getManager();
        $em->persist($agendamento);
        $em->flush();

        return $this->json($agendamento);
    }
}
