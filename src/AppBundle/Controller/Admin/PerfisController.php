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

use AppBundle\Form\CargoType as EntityType;
use Mangati\BaseBundle\Controller\CrudController;
use Mangati\BaseBundle\Event\CrudEvent;
use Mangati\BaseBundle\Event\CrudEvents;
use Novosga\Entity\Cargo as Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DefaultController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 * 
 * @Route("/admin/perfis")
 */
class PerfisController extends CrudController
{
    
    public function __construct()
    {
        parent::__construct(Entity::class);
    }

    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/", name="admin_perfis_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/perfis/index.html.twig', [
            'tab' => 'perfis',
        ]);
    }
   
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/search.json", name="admin_perfis_search")
     */
    public function searchAction(Request $request) 
    {
        $query = $this
                ->getDoctrine()
                ->getManager()
                ->createQueryBuilder()
                ->select('e')
                ->from(Entity::class, 'e')
                ->getQuery();
        
        return $this->dataTable($request, $query, false);
    }
    
    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/edit/{id}", name="admin_perfis_edit")
     */
    public function editAction(Request $request, $id = 0)
    {
        $this->addEventListener(CrudEvents::FORM_RENDER, function (CrudEvent $event) {
            $params = $event->getData();
            $params['tab'] = 'perfis';
        });
        
        return $this->edit('admin/perfis/edit.html.twig', $request, $id);
    }
    
    protected function createFormType()
    {
        return EntityType::class;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function editFormOptions(Request $request, $entity)
    {
        $options = parent::editFormOptions($request, $entity);
        
        $kernel = $this->get('kernel');
        $modulos = array_filter($kernel->getBundles(), function ($module) {
            return ($module instanceof \Novosga\Module\ModuleInterface);
        });
        
        return array_merge($options, [
            'modulos' => $modulos
        ]);
    }
}