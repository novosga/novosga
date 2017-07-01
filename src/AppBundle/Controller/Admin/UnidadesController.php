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

use Exception;
use Novosga\Entity\Unidade;
use Novosga\Http\Envelope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UnidadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/unidades")
 */
class UnidadesController extends Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_unidades_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $entity = null;
            
        if ($request->get('id') > 0) {
            $entity = $em->find(Unidade::class, $request->get('id'));
        }

        if (!$entity) {
            $entity = new Unidade();
        }

        $form = $this->getForm($entity);
        
        if ($request->isMethod('POST')) {
            try {
                $form->handleRequest($request);
            
                if (!$form->isValid()) {
                    $message = '';
                    foreach ($form->getErrors(true) as $error) {
                        $message .= $error->getMessage();
                    }
                    throw new Exception($message);
                }

                if ($entity->getId()) {
                    $em->merge($entity);
                } else {
                    $entity->getImpressao()->setCabecalho(_('Novo SGA'));
                    $entity->getImpressao()->setRodape(_('========'));
                    
                    $em->persist($entity);
                }

                $em->flush();
                
                return $this->redirectToRoute('admin_unidades_index');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('admin/unidades/index.html.twig', [
            'tab' => 'unidades',
            'entity' => $entity,
            'form' => $form->createView()
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/list", name="admin_unidades_list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $unidades = $em
                ->getRepository(Unidade::class)
                ->findBy([], ['nome' => 'ASC']);
        
        $envelope = new Envelope();
        $envelope->setData($unidades);
        
        return $this->json($envelope);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/delete/{id}", name="admin_unidades_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, Unidade $unidade)
    {
        $envelope = new Envelope();
        
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($unidade);
            $em->flush();
            
            $envelope->setData($unidade);
        } catch (Exception $e) {
            $envelope->exception($e);
        }
        
        return $this->json($envelope);
    }
    
    private function getForm($entity)
    {
        $form = $this->createForm(\AppBundle\Form\UnidadeType::class, $entity, [
            'action' => $this->generateUrl('admin_unidades_index')
        ]);
        
        return $form;
    }
}
