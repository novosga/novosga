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

namespace App\Controller;

use App\Entity\Unidade;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * PainelViewController - Display panel with playlist
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/painel')]
class PainelViewController extends AbstractController
{
    /**
     * Display panel with ticket playlist and optional animations
     */
    #[Route('/display/{id}', name: 'painel_display', methods: ['GET'])]
    public function display(Request $request, Unidade $unidade): Response
    {
        $servicos = $request->query->get('servicos', '');
        $transition = $request->query->get('transition', 'default');
        $interval = (int) $request->query->get('interval', 5000);

        return $this->render('painel/display.html.twig', [
            'unidade' => $unidade,
            'servicos' => $servicos,
            'transition' => $transition,
            'interval' => $interval,
        ]);
    }
}
