<?php

namespace Novosga\RolesBundle\Controller;

use Novosga\Entity\Cargo as Entity;
use AppBundle\Form\CargoType as EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Mangati\BaseBundle\Controller\CrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Novosga\Entity\TreeModel;

/**
 * DefaultController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 * 
 */
class DefaultController extends CrudController
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
     * @Route("/", name="novosga_roles_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('NovosgaRolesBundle:default:index.html.twig');
    }
   
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/search.json", name="novosga_roles_search")
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
     * @Route("/edit/{id}", name="novosga_roles_edit")
     */
    public function editAction(Request $request, $id = 0)
    {
        return $this->edit('NovosgaRolesBundle:default:edit.html.twig', $request, $id);
    }
    
    protected function createFormType()
    {
        return EntityType::class;
    }
}
