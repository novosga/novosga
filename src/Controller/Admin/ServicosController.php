<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Form\ServicoType as EntityType;
use Novosga\Entity\Servico as Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ServicosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/servicos")
 */
class ServicosController extends Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_servicos_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $servicos = $this
                ->getDoctrine()
                ->getManager()
                ->createQueryBuilder()
                ->select('e')
                ->from(Entity::class, 'e')
                ->where('e.mestre IS NULL')
                ->getQuery()
                ->getResult();
        
        return $this->render('admin/servicos/index.html.twig', [
            'tab'      => 'servicos',
            'servicos' => $servicos,
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="admin_servicos_new")
     * @Route("/{id}", name="admin_servicos_edit")
     * @Method({"GET","POST"})
     */
    public function formAction(Request $request, Entity $entity = null)
    {
        if (!$entity) {
            $entity = new Entity();
        }
        
        $form = $this->createForm(EntityType::class, $entity);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $trans = $this->get('translator');
            
            $this->addFlash('success', $trans->trans('Serviço salvo com sucesso!'));
            
            return $this->redirectToRoute('admin_servicos_edit', [ 'id' => $entity->getId() ]);
        }
        
        return $this->render('admin/servicos/form.html.twig', [
            'tab'    => 'servicos',
            'entity' => $entity,
            'form'   => $form->createView(),
        ]);
    }
}
