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

use App\Form\ApplicationSettingsFormType;
use App\Service\ApplicationService;
use Novosga\Http\Envelope;
use App\Service\AtendimentoService;
use Novosga\Entity\UsuarioInterface;
use Novosga\Service\FileUploaderServiceInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        ApplicationService $service,
        FileUploaderServiceInterface $fileUploader,
    ): Response {
        $app = $service->loadSettings();
        $form = $this
            ->createForm(ApplicationSettingsFormType::class, $app)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoNavbarFile = $form->get('appearance')->get('logoNavbar')->getData();
            if ($logoNavbarFile instanceof UploadedFile) {
                $app->appearance->logoNavbar = $fileUploader->upload($logoNavbarFile, 'logo-navbar');
            }
            $logoLoginFile = $form->get('appearance')->get('logoLogin')->getData();
            if ($logoLoginFile instanceof UploadedFile) {
                $app->appearance->logoLogin = $fileUploader->upload($logoLoginFile, 'logo-login');
            }

            $service->saveSettings($app);

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/index.html.twig', [
            'tab' => 'index',
            'form' => $form,
        ]);
    }

    #[Route("/remove-settings-file", name: "remove_settings_file", methods: ['DELETE'])]
    public function removeFile(Request $request, ApplicationService $service): Response
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
}
