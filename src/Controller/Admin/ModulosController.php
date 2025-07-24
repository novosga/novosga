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

namespace App\Controller\Admin;

use App\Service\ModuleService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * ModulosController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/modulos', name: 'admin_modulos_')]
class ModulosController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ModuleService $service): Response
    {
        $modules = $service->getInstalledModules();

        return $this->render('admin/modulos/index.html.twig', [
            'tab' => 'modulos',
            'modules' => $modules,
        ]);
    }
}
