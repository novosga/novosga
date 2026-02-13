<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Dto\ChamarAtendimento;
use App\Dto\EncerrarAtendimento;
use App\Entity\Atendimento;
use App\Entity\Local;
use App\Service\AtendimentoService;
use Exception;
use Novosga\Entity\UsuarioInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @extends ApiCrudController<Atendimento>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 */
#[Route('/api/atendimentos')]
class AtendimentosController extends ApiCrudController
{
    use Actions\GetTrait;
    use Actions\FindTrait;

    public function getEntityName(): string
    {
        return Atendimento::class;
    }

    /**
     * Chama um atendimento.
     */
    #[Route('/{id}/chamar', methods: ['POST'])]
    public function chamar(
        Atendimento $atendimento,
        #[MapRequestPayload] ChamarAtendimento $dto,
        AtendimentoService $service,
        LoggerInterface $logger,
    ): Response {
        try {
            /** @var UsuarioInterface */
            $usuario = $this->getUser();

            $local = $this->getManager()->getRepository(Local::class)->find($dto->local);
            if (!$local) {
                $error = $this->translate('error.invalid_location');
                throw new Exception($error);
            }

            $success = $service->chamarAtendimento(
                $atendimento,
                $usuario,
                $local,
                $dto->numeroLocal
            );

            if (!$success) {
                $error = $this->translate('error.api.ticket_call_failed');
                throw new Exception($error);
            }

            $status = 200;
            $response = $atendimento;
        } catch (Exception $ex) {
            $response = [
                'error' => $ex->getMessage(),
            ];
            $status = 422;

            $logger->error('[/api/atendimentos/{id}/chamar] ' . $ex->getMessage());
        }

        return $this->json($response, $status);
    }

    /**
     * Inicia um atendimento.
     */
    #[Route('/{id}/iniciar', methods: ['POST'])]
    public function iniciar(
        Atendimento $atendimento,
        AtendimentoService $service,
        LoggerInterface $logger,
    ): Response {
        try {
            /** @var UsuarioInterface */
            $usuario = $this->getUser();

            $service->iniciarAtendimento($atendimento, $usuario);

            $status = 200;
            $response = $atendimento;
        } catch (Exception $ex) {
            $response = [
                'error' => $ex->getMessage(),
            ];
            $status = 422;

            $logger->error('[/api/atendimentos/{id}/iniciar] ' . $ex->getMessage());
        }

        return $this->json($response, $status);
    }

    /**
     * Encerra um atendimento.
     */
    #[Route('/{id}/encerrar', methods: ['POST'])]
    public function encerrar(
        Atendimento $atendimento,
        #[MapRequestPayload] EncerrarAtendimento $dto,
        AtendimentoService $service,
        LoggerInterface $logger,
    ): Response {
        try {
            /** @var UsuarioInterface */
            $usuario = $this->getUser();

            $service->encerrar(
                $atendimento,
                $usuario,
                $dto->servicosRealizados,
                $dto->servicoRedirecionado,
                $dto->novoUsuario
            );

            $status = 200;
            $response = $atendimento;
        } catch (Exception $ex) {
            $response = [
                'error' => $ex->getMessage(),
            ];
            $status = 422;

            $logger->error('[/api/atendimentos/{id}/encerrar] ' . $ex->getMessage());
        }

        return $this->json($response, $status);
    }
}
