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

use Exception;
use Novosga\Entity\Prioridade as Entity;
use App\Form\PrioridadeType as EntityType;
use Novosga\Http\Envelope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PrioridadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/prioridades")
 */
class PrioridadesController extends Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_prioridades_index")
     */
    public function indexAction(Request $request)
    {
        $prioridades = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Entity::class)
                ->findBy([], ['nome' => 'ASC']);
        
        return $this->render('admin/prioridades/index.html.twig', [
            'tab' => 'prioridades',
            'prioridades' => $prioridades,
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="admin_prioridades_new")
     * @Route("/{id}", name="admin_prioridades_edit")
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
            
            $this->addFlash('success', $trans->trans('Local salvo com sucesso!'));
            
            return $this->redirectToRoute('admin_prioridades_edit', [ 'id' => $entity->getId() ]);
        }
        
        return $this->render('admin/prioridades/form.html.twig', [
            'tab'    => 'prioridades',
            'entity' => $entity,
            'form'   => $form->createView(),
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/{id}", name="admin_prioridades_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Entity $prioridade)
    {
        $envelope = new Envelope();
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($prioridade);
        $em->flush();

        $envelope->setData($prioridade);
        
        return $this->json($envelope);
    }
}
