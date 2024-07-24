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

use App\Entity\Unidade;
use App\Entity\PainelSenha;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PainelController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
#[Route('/api')]
class PainelController extends ApiControllerBase
{
    /**
     * Retorna as senhas para serem exibidas no painel (max result 10).
     */
    #[Route('/unidades/{id}/painel', methods: ['GET'])]
    public function painel(Request $request, Unidade $unidade): Response
    {
        $servicos = explode(',', $request->get('servicos'));

        $senhas = $this
            ->getManager()
            ->createQueryBuilder()
            ->select(['e', 's'])
            ->from(PainelSenha::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->andWhere('s.id IN (:servicos)')
            ->orderBy('e.id', 'DESC')
            ->setParameter('unidade', $unidade)
            ->setParameter('servicos', $servicos)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        return $this->json($senhas);
    }
}
