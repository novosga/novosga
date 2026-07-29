<?php

declare(strict_types=1);

namespace App\NovosgaChamadaSenhaBundle\Controller;

use Exception;
use Novosga\Entity\UsuarioInterface;
use Novosga\Http\Envelope;
use Novosga\Repository\LocalRepositoryInterface;
use Novosga\Repository\ServicoRepositoryInterface;
use Novosga\Repository\UsuarioRepositoryInterface;
use Novosga\Service\AtendimentoServiceInterface;
use Novosga\Service\FilaServiceInterface;
use Novosga\Service\UsuarioServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/", name: "novosga_chamadasenha_")]
class DefaultController extends AbstractController
{
    #[Route("/", name: "index", methods: ["GET"])]
    public function index(UsuarioServiceInterface $usuarioService, LocalRepositoryInterface $localRepository): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $unidade = $usuario->getLotacao()->getUnidade();

        $themeFile = '/mnt/media/config/theme_' . $unidade->getId() . '.json';
        if (!file_exists($themeFile)) {
            $themeFile = '/mnt/media/config/theme.json';
        }
        $theme        = file_exists($themeFile) ? (json_decode(file_get_contents($themeFile), true) ?? []) : [];
        $primary      = $theme['primary']   ?? '#003580';
        $accent       = $theme['accent']    ?? '#007A3D';
        $alert        = $theme['alert']     ?? '#C8102E';
        $titulo       = $theme['titulo']    ?? 'Sistema de Atendimento — NovoSGA';
        $subtitulo    = $theme['subtitulo'] ?? '';
        $browserTitle = !empty($theme['browser_title']) ? $theme['browser_title'] : ($titulo . ($subtitulo ? ' — ' . $subtitulo : ''));
        $favicon      = !empty($theme['favicon']) ? '/media/config/' . $theme['favicon'] : (!empty($theme['logo_totem']) ? '/media/config/' . $theme['logo_totem'] : null);
        $logoTotem    = !empty($theme['logo_totem']) ? '/media/config/' . $theme['logo_totem'] : null;

        $localMeta = $usuarioService->meta($usuario, 'atendimento.local');
        $localEntity = null;
        if ($localMeta && $localMeta->getValue()) {
            $localIdVal = (int)$localMeta->getValue();
            if ($localIdVal > 0) {
                $localEntity = $localRepository->find($localIdVal);
            }
        }
        if (!$localEntity) {
            $localEntity = $localRepository->findOneBy([]);
            if ($localEntity) {
                $usuarioService->meta($usuario, 'atendimento.local', $localEntity->getId());
            }
        }
        $localNome = $localEntity ? $localEntity->getNome() : 'Guichê';

        $numMeta = $usuarioService->meta($usuario, 'atendimento.num_local');
        $numeroLocal = ($numMeta && $numMeta->getValue()) ? (int)$numMeta->getValue() : '';
        $missingLocal = (!$numMeta || !$numMeta->getValue());

        return $this->render('@NovosgaChamadaSenha/default/index.html.twig', [
            'missingLocal' => $missingLocal,
            'localNome'    => $localNome,
            'numeroLocal'  => $numeroLocal,
            'primary'      => $primary,
            'accent'       => $accent,
            'alert'        => $alert,
            'titulo'       => $titulo,
            'subtitulo'    => $subtitulo,
            'browserTitle' => $browserTitle,
            'favicon'      => $favicon,
            'logoTotem'    => $logoTotem,
        ]);
    }

    #[Route("/set_num_local", name: "setnumlocal", methods: ["POST"])]
    public function setNumLocal(
        Request $request,
        UsuarioServiceInterface $usuarioService,
        LocalRepositoryInterface $localRepository
    ): Response {
        $body = json_decode($request->getContent(), true) ?? [];
        $numLocal = (int)($body['num_local'] ?? 1);
        if ($numLocal < 1) $numLocal = 1;

        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $usuarioService->meta($usuario, 'atendimento.num_local', $numLocal);

        $localMeta = $usuarioService->meta($usuario, 'atendimento.local');
        if (!$localMeta || !$localMeta->getValue()) {
            $firstLocal = $localRepository->findOneBy([]);
            if ($firstLocal) {
                $usuarioService->meta($usuario, 'atendimento.local', $firstLocal->getId());
            }
        }

        return $this->json(['success' => true, 'num_local' => $numLocal]);
    }

    #[Route("/ajax_update", name: "ajaxupdate", methods: ["GET"])]
    public function ajaxUpdate(
        FilaServiceInterface $filaService,
        AtendimentoServiceInterface $atendimentoService,
        UsuarioServiceInterface $usuarioService,
        LocalRepositoryInterface $localRepository
    ): Response {
        $envelope = new Envelope();

        /** @var UsuarioInterface */
        $usuario  = $this->getUser();
        $unidade  = $usuario->getLotacao()->getUnidade();

        $localId     = $this->getLocalAtendimento($usuarioService, $usuario);
        $numeroLocal = $this->getNumeroLocalAtendimento($usuarioService, $usuario);

        $localIdVal = (int)($localId ?? 0);
        $local = ($localIdVal > 0) ? $localRepository->find($localIdVal) : null;
        if (!$local) { $local = $localRepository->findOneBy([]) ?: null; }
        $tipo = $this->getTipoAtendimento($usuarioService, $usuario);

        $servicos = $usuarioService->getServicosUnidade($usuario, $unidade);

        $atendimentos = $filaService->getFilaAtendimento($unidade, $usuario, $servicos, $tipo);
        $total = count($atendimentos);

        $filas = [];
        $filas[] = [
            'atendimentos' => $atendimentos,
        ];

        foreach ($servicos as $servico) {
            $atendimentosServico = $filaService->getFilaAtendimento($unidade, $usuario, [ $servico ], $tipo);
            $filas[] = [
                'servico' => $servico,
                'atendimentos' => $atendimentosServico,
            ];
        }

        $atendimentoAtual = $atendimentoService->getAtendimentoAndamento($usuario->getId(), $unidade);

        $data = [
            'total' => $total,
            'fila' => $atendimentos,
            'filas' => $filas,
            'atendimento' => $atendimentoAtual,
            'usuario' => [
                'id' => $usuario->getId(),
                'local' => $local,
                'numeroLocal' => $numeroLocal,
                'tipoAtendimento' => $tipo,
            ],
        ];

        $envelope->setData($data);

        return $this->json($envelope);
    }

    #[Route("/chamar", name: "chamar", methods: ["POST"])]
    public function chamar(
        LocalRepositoryInterface $localRepository,
        AtendimentoServiceInterface $atendimentoService,
        UsuarioServiceInterface $usuarioService,
    ): Response {
        $envelope = new Envelope();
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $unidade = $usuario->getLotacao()->getUnidade();

        $atendimento = $atendimentoService->getAtendimentoAndamento($usuario->getId(), $unidade);

        if (!$atendimento) {
            $localId = $this->getLocalAtendimento($usuarioService, $usuario);
            $numeroLocal = $this->getNumeroLocalAtendimento($usuarioService, $usuario);
            $servicos = $usuarioService->getServicosUnidade($usuario, $unidade);

            $localIdVal = (int)($localId ?? 0);
            $local = ($localIdVal > 0) ? $localRepository->find($localIdVal) : null;
            if (!$local) { $local = $localRepository->findOneBy([]) ?: null; }
            $tipo = $this->getTipoAtendimento($usuarioService, $usuario);

            $atendimento = $atendimentoService->chamarProximo(
                $unidade,
                $usuario,
                $local,
                $tipo,
                $servicos,
                $numeroLocal,
            );
        }

        if (!$atendimento) {
            throw new Exception('Fila vazia');
        }

        $atendimentoService->chamarSenha($atendimento, $usuario);

        $data = $atendimento->jsonSerialize();
        $envelope->setData($data);

        return $this->json($envelope);
    }

    #[Route("/iniciar", name: "iniciar", methods: ["POST"])]
    public function iniciar(AtendimentoServiceInterface $atendimentoService): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $unidade = $usuario->getLotacao()->getUnidade();
        $atual   = $atendimentoService->getAtendimentoAndamento($usuario->getId(), $unidade);

        if (!$atual) {
            throw new Exception('Nenhum atendimento em andamento');
        }

        $atendimentoService->iniciarAtendimento($atual, $usuario);

        $data     = $atual->jsonSerialize();
        $envelope = new Envelope();
        $envelope->setData($data);

        return $this->json($envelope);
    }

    #[Route("/nao_compareceu", name: "naocompareceu", methods: ["POST"])]
    public function naoCompareceu(AtendimentoServiceInterface $atendimentoService): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $unidade = $usuario->getLotacao()->getUnidade();
        $atual   = $atendimentoService->getAtendimentoAndamento($usuario->getId(), $unidade);

        if (!$atual) {
            throw new Exception('Nenhum atendimento em andamento');
        }

        $atendimentoService->naoCompareceu($atual, $usuario);

        $data = $atual->jsonSerialize();
        $envelope = new Envelope();
        $envelope->setData($data);

        return $this->json($envelope);
    }

    #[Route("/encerrar", name: "encerrar", methods: ["POST"])]
    public function encerrar(
        Request $request,
        UsuarioRepositoryInterface $usuarioRepository,
        ServicoRepositoryInterface $servicoRepository,
        AtendimentoServiceInterface $atendimentoService,
    ): Response {
        $envelope = new Envelope();
        $body = json_decode($request->getContent(), true) ?? [];

        /** @var UsuarioInterface */
        $usuario = $this->getUser();
        $unidade = $usuario->getLotacao()->getUnidade();
        $atual   = $atendimentoService->getAtendimentoAndamento($usuario->getId(), $unidade);

        if (!$atual) {
            throw new Exception('Nenhum atendimento em andamento');
        }

        $servicosIds = $body['servicos'] ?? [];
        if (empty($servicosIds)) {
            $servicosIds = [$atual->getServico()->getId()];
        }

        $novoUsuario = null;
        $servicoRedirecionado = null;
        $redirecionar = $body['redirecionar'] ?? false;
        if ($redirecionar) {
            $servicoRedirecionado = $servicoRepository->find($body['novoServico'] ?? null);
            if (isset($body['novoUsuario'])) {
                $novoUsuario = $usuarioRepository->find($body['novoUsuario']);
            }
        }

        $resolucao = $body['resolucao'] ?? null;
        if ($resolucao === 'resolvido') {
            $atual->setResolucao(AtendimentoServiceInterface::RESOLVIDO);
        } else {
            $atual->setResolucao(AtendimentoServiceInterface::PENDENTE);
        }

        $observacao = $body['observacao'] ?? null;
        if ($observacao) {
            $atual->setObservacao($observacao);
        }

        $servicosReais = [];
        foreach ($servicosIds as $sId) {
            $s = $servicoRepository->find($sId);
            if ($s) {
                $servicosReais[] = $s;
            }
        }

        $atendimentoService->encerrar($atual, $usuario, $servicosReais, $servicoRedirecionado, $novoUsuario);

        return $this->json($envelope);
    }

    #[Route("/customer/{id}", name: "customer", methods: ["GET"])]
    public function customer(
        AtendimentoServiceInterface $atendimentoService,
        int $id,
    ): Response {
        $atendimento = $atendimentoService->getById($id);
        if (!$atendimento) {
            return $this->json(new Envelope(null));
        }
        $cliente = $atendimento->getCliente();
        return $this->json(new Envelope($cliente));
    }

    private function getLocalAtendimento(UsuarioServiceInterface $usuarioService, $usuario): ?int
    {
        $localMeta = $usuarioService->meta($usuario, 'atendimento.local');
        return $localMeta ? (int) $localMeta->getValue() : null;
    }

    private function getNumeroLocalAtendimento(UsuarioServiceInterface $usuarioService, $usuario): ?int
    {
        $numeroLocalMeta = $usuarioService->meta($usuario, 'atendimento.num_local');
        return $numeroLocalMeta ? (int) $numeroLocalMeta->getValue() : null;
    }

    private function getTipoAtendimento(UsuarioServiceInterface $usuarioService, $usuario): ?string
    {
        $tipoAtendimentoMeta = $usuarioService->meta($usuario, 'atendimento.tipo');
        return $tipoAtendimentoMeta ? $tipoAtendimentoMeta->getValue() : 'todos';
    }
}
