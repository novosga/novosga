<?php

namespace AppBundle\Controller\Admin;

use Exception;
use Novosga\Entity\Unidade;
use Novosga\Http\Envelope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
                    $entity->setMensagemImpressao('');
                    $em->persist($entity);
                }

                $em->flush();
                
                return $this->redirectToRoute('admin_unidades_index');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('admin/unidades.html.twig', [
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
        
        return new JsonResponse($unidades);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/delete/{id}", name="admin_unidades_delete")
     */
    public function deleteAction(Request $request, Unidade $unidade)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($unidade);
            $em->flush();
            
            return new JsonResponse();
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), false);
        }
    }
    
    private function getForm($entity)
    {
        $form = $this->createForm(\AppBundle\Form\UnidadeType::class, $entity, [
            'action' => $this->generateUrl('admin_unidades_index')
        ]);
        
        return $form;
    }

}
