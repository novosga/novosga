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

use App\Entity\Painel;
use App\Entity\PainelSenha;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\PainelServicoInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Service\PainelServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PainelController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/painel', name: 'painel_')]
class PainelController extends AbstractController
{
    #[Route('/{publicId:painel}', name: 'show', methods: ['GET'])]
    public function show(
        Painel $painel,
        ParameterBagInterface $params,
        PainelServiceInterface $painelService,
    ): Response {

        return $this->render('painel/show.html.twig', [
            'painel' => $painel,
            'mercureUrl' => $params->get('mercure_url'),
            'settings' => $painelService->loadSettings($painel),
        ]);
    }

    #[Route('/{publicId:painel}/data', name: 'data', methods: ['GET'])]
    public function data(
        Painel $painel,
        EntityManagerInterface $em,
    ): Response {
        $servicos = $painel
            ->getServicos()
            ->map(fn (PainelServicoInterface $ps) => $ps->getServico())
            ->map(fn (ServicoInterface $s) => $s->getId())
            ->toArray();

        $senhas = $em
            ->createQueryBuilder()
            ->select(['e', 's'])
            ->from(PainelSenha::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->andWhere('s.id IN (:servicos)')
            ->orderBy('e.id', 'DESC')
            ->setParameter('unidade', $painel->getUnidade())
            ->setParameter('servicos', $servicos)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->json($senhas);
    }
}
