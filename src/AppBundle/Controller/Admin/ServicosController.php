<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Form\ServicoType;
use Mangati\BaseBundle\Controller\CrudController;
use Mangati\BaseBundle\Event\CrudEvent;
use Mangati\BaseBundle\Event\CrudEvents;
use Novosga\Entity\Servico;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * ServicosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 * 
 * @Route("/admin/servicos")
 */
class ServicosController extends CrudController
{
    
    public function __construct() {
        parent::__construct(Servico::class);
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/", name="admin_servicos_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/servicos/index.html.twig', [
            'tab' => 'servicos',
        ]);
    }
    
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/search.json", name="admin_servicos_search")
     */
    public function searchAction(Request $request) 
    {
        $query = $this
                ->getDoctrine()
                ->getManager()
                ->createQueryBuilder()
                ->select('e')
                ->from(Servico::class, 'e')
                ->where('e.mestre IS NULL')
                ->getQuery();
        
        return $this->dataTable($request, $query, false);
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/edit/{id}", name="admin_servicos_edit")
     */
    public function editAction(Request $request, $id = 0)
    {
        $this->addEventListener(CrudEvents::FORM_RENDER, function (CrudEvent $event) {
            $params = $event->getData();
            $params['tab'] = 'servicos';
        });
        
        return $this->edit('admin/servicos/edit.html.twig', $request, $id);
    }

    protected function createFormType() 
    {
        return ServicoType::class;
    }
}
