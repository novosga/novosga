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

use App\Kernel;
use App\Service\ModuleService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ModulosController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/modulos', name: 'admin_modulos_')]
class ModulosController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        Kernel $kernel,
        ModuleService $service,
        TranslatorInterface $translator,
    ): Response {
        $modules = array_map(function ($module) use ($translator) {
            $name = $translator->trans($module->getDisplayName(), [], $module->getName());
            return [
                'active' => true,
                'key' => $module->getKeyName(),
                'name' => $name,
            ];
        }, $service->filterModules($kernel->getBundles()));

        return $this->render('admin/modulos/index.html.twig', [
            'tab' => 'modulos',
            'modules' => $modules,
        ]);
    }
}
