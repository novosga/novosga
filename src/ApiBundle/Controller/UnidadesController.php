<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Controller;

use Exception;
use Novosga\Service\ServicoService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * UnidadesController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/unidades")
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
        parent::__construct(\Novosga\Entity\Unidade::class);
    }
    
    /**
     * @Route("/{id}/servicos")
     * @Method("GET")
     */
    public function servicosAction($id)
    {
        try {
            $unidade = $this->getRepository()->find($id);
            
            if (!$unidade) {
                throw new NotFoundHttpException;
            }
            
            $em = $this->getDoctrine()->getManager();
            
            $service = new ServicoService($em);
            $servicos = $service->servicosUnidade($unidade, 'e.status = 1');
            
            $response = $servicos;
            
        } catch (Exception $e) {
            $response = [
                'error' => $e->getMessage()
            ];
        }
        
        return $this->json($response);
    }
}
