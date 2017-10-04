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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
    
    public function __construct()
    {
        parent::__construct(Unidade::class);
    }
    
    /**
     * @Route("/{id}/servicos")
     * @Method("GET")
     */
    public function servicosAction(Unidade $unidade)
    {
        $em = $this->getDoctrine()->getManager();

        $service = new ServicoService($em);
        $servicos = $service->servicosUnidade($unidade, 'e.ativo = TRUE');

        return $this->json($servicos);
    }
    
    /**
     * @Route("/{id}/atendimentos")
     * @Method("GET")
     */
    public function atendimentosAction(Unidade $unidade)
    {
        $em = $this->getDoctrine()->getManager();

        $atendimentos = $em
                ->createQueryBuilder()
                ->select([
                    'e', 'su', 's', 'ut', 'u'
                ])
                ->from(Atendimento::class, 'e')
                ->join('e.servicoUnidade', 'su')
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
