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

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * DefaultController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class DefaultController extends AbstractController
{
    #[Route('/api')]
    public function index(ParameterBagInterface $params): Response
    {
        return $this->json([
            'status' => 'ok',
            'time' => time(),
            'mercureUrl' => $params->get('mercure_url'),
        ]);
    }
}
