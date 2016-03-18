<?php

namespace Novosga\ServicesBundle\Controller;

use Novosga\Entity\Servico;
use Mangati\BaseBundle\Controller\CrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * ServicosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultController extends CrudController
{
    
    public function __construct() {
        parent::__construct(Servico::class);
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/", name="novosga_services_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('NovosgaServicesBundle:Default:index.html.twig');
    }
    
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/search.json", name="novosga_services_search")
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
     * @Route("/edit/{id}", name="novosga_services_edit")
     */
    public function editAction(Request $request, $id = 0)
    {
        return $this->edit('NovosgaServicesBundle:Default:edit.html.twig', $request, $id);
    }

    protected function createFormType() 
    {
        return \AppBundle\Form\ServicoType::class;
    }
}
