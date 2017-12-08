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
use Novosga\Entity\Unidade as Entity;
use App\Form\UnidadeType as EntityType;
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
        $unidades = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Entity::class)
                ->findBy([], ['nome' => 'ASC']);
        
        return $this->render('admin/unidades/index.html.twig', [
            'tab'      => 'unidades',
            'unidades' => $unidades,
        ]);
    }
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="admin_unidades_new")
     * @Route("/{id}", name="admin_unidades_edit")
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
            if (!$entity->getId()) {
                $entity->getImpressao()->setCabecalho('Novo SGA');
                $entity->getImpressao()->setRodape('========');
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $trans = $this->get('translator');
            
            $this->addFlash('success', $trans->trans('Serviço salvo com sucesso!'));
            
            return $this->redirectToRoute('admin_unidades_edit', [ 'id' => $entity->getId() ]);
        }
        
        return $this->render('admin/unidades/form.html.twig', [
            'tab'    => 'unidades',
            'entity' => $entity,
            'form'   => $form->createView(),
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/{id}", name="admin_unidades_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Entity $unidade)
    {
        $trans = $this->get('translator');
        
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($unidade);
            $em->flush();
        
            $this->addFlash('success', $trans->trans('Unidade removida com sucesso!'));
            
            return $this->redirectToRoute('admin_unidades_index');
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'A unidade não pode ser removida porque está sendo utilizada.';
            } else {
                $message = $e->getMessage();
            }
            
            $this->addFlash('error', $trans->trans($message));
            
            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
