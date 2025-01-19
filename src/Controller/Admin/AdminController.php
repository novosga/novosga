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

namespace App\Controller\Admin;

use App\Form\Settings\AppearanceSettingsFormType;
use App\Form\Settings\BehaviorSettingsFormType;
use App\Form\Settings\QueueSettingsFormType;
use App\Service\ApplicationSettingsService;
use App\Service\AtendimentoService;
use Novosga\Http\Envelope;
use Novosga\Entity\UsuarioInterface;
use Novosga\Service\FileUploaderServiceInterface;
use Novosga\Settings\AppearanceSettings;
use Novosga\Settings\BehaviorSettings;
use Novosga\Settings\QueueSettings;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AdminController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        ApplicationSettingsService $service,
        FileUploaderServiceInterface $fileUploader,
    ): Response {
        $app = $service->loadSettings();
        $appearanceForm = $this->createAppearanceSettingsForm($request, $app->appearance);
        $behaviorForm = $this->createBehaviorSettingsForm($request, $app->behavior);
        $queueForm = $this->createQueueSettingsForm($request, $app->queue);

        if ($appearanceForm->isSubmitted() && $appearanceForm->isValid()) {
            $logoNavbarFile = $appearanceForm->get('logoNavbar')->getData();
            if ($logoNavbarFile instanceof UploadedFile) {
                $app->appearance->logoNavbar = $fileUploader->upload($logoNavbarFile, 'logo-navbar');
            }
            $logoLoginFile = $appearanceForm->get('logoLogin')->getData();
            if ($logoLoginFile instanceof UploadedFile) {
                $app->appearance->logoLogin = $fileUploader->upload($logoLoginFile, 'logo-login');
            }

            $service->saveAppearanceSettings($app->appearance);

            $this->addFlash('success', 'Configuração de aparência salva com sucesso');

            return $this->redirectToRoute('admin_index');
        }

        if ($behaviorForm->isSubmitted() && $behaviorForm->isValid()) {
            $service->saveBehaviorSettings($app->behavior);

            $this->addFlash('success', 'Configuração de comportamento salva com sucesso');

            return $this->redirectToRoute('admin_index');
        }

        if ($queueForm->isSubmitted() && $queueForm->isValid()) {
            $service->saveQueueSettings($app->queue);

            $this->addFlash('success', 'Configuração de ordenação da fila salva com sucesso');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/index.html.twig', [
            'tab' => 'index',
            'appearanceForm' => $appearanceForm,
            'behaviorForm' => $behaviorForm,
            'queueForm' => $queueForm,
        ]);
    }

    #[Route("/remove-settings-file", name: "remove_settings_file", methods: ['DELETE'])]
    public function removeFile(Request $request, ApplicationSettingsService $service): Response
    {
        $key = $request->get('key');
        switch ($key) {
            case 'logoNavbar':
            case 'logoLogin':
                $app = $service->loadSettings();
                if ($key === 'logoNavbar') {
                    $app->appearance->logoNavbar = '';
                } else {
                    $app->appearance->logoLogin = '';
                }
                $service->saveSettings($app);
                break;
        }

        return $this->json([ 'success' => true ]);
    }

    #[Route('/acumular_atendimentos', name: 'acumular_atendimentos', methods: ['POST'])]
    public function acumularAtendimentos(AtendimentoService $service, ClockInterface $clock): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();

        $envelope = new Envelope();
        $service->acumularAtendimentos($usuario, null, $clock->now());

        return $this->json($envelope);
    }

    #[Route('/limpar_atendimentos', name: 'limpar_atendimentos', methods: ['POST'])]
    public function limparAtendimentos(AtendimentoService $service): Response
    {
        /** @var UsuarioInterface */
        $usuario = $this->getUser();

        $envelope = new Envelope();
        $service->limparDados($usuario, null);

        return $this->json($envelope);
    }

    private function createAppearanceSettingsForm(Request $request, AppearanceSettings $settings): FormInterface
    {
        return $this
            ->createForm(AppearanceSettingsFormType::class, $settings)
            ->handleRequest($request);
    }

    private function createBehaviorSettingsForm(Request $request, BehaviorSettings $settings): FormInterface
    {
        return $this
            ->createForm(BehaviorSettingsFormType::class, $settings)
            ->handleRequest($request);
    }

    private function createQueueSettingsForm(Request $request, QueueSettings $settings): FormInterface
    {
        return $this
            ->createForm(QueueSettingsFormType::class, $settings)
            ->handleRequest($request);
    }
}
