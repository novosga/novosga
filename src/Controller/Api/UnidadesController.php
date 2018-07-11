<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use Novosga\Entity\Unidade;
use Novosga\Entity\Atendimento;
use Novosga\Service\ServicoService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UnidadesController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 * @Route("/api/unidades")
 */
class UnidadesController extends ApiCrudController
{
    use Actions\GetTrait,
        Actions\FindTrait,
        Actions\PostTrait,
        Actions\PutTrait,
        Actions\DeleteTrait;

    public function getEntityName()
    {
        return Unidade::class;
    }

    /**
     * @Route("/{id}/servicos", methods={"GET"})
     */
    public function servicos(Unidade $unidade, ServicoService $service)
    {
        $servicos = $service->servicosUnidade($unidade, ['ativo' => true]);

        return $this->json($servicos);
    }

    /**
     * @Route("/{id}/atendimentos", methods={"GET"})
     */
    public function atendimentos(Unidade $unidade)
    {
        $atendimentos = $this
            ->getDoctrine()
            ->getManager()
            ->createQueryBuilder()
            ->select([
                'e', 's', 'ut', 'u'
            ])
            ->from(Atendimento::class, 'e')
            ->join('e.servico', 's')
            ->join('e.usuarioTriagem', 'ut')
            ->leftJoin('e.usuario', 'u')
            ->where('e.unidade = :unidade')
            ->orderBy('e.id', 'ASC')
            ->setParameters([
                'unidade' => $unidade,
            ])
            ->getQuery()
            ->getResult();

        return $this->json($atendimentos);
    }
}
