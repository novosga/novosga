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

use Novosga\Entity\Prioridade as Entity;
use App\Form\PrioridadeType as EntityType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PrioridadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/prioridades")
 */
class PrioridadesController extends AbstractController
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_prioridades_index")
     */
    public function index(Request $request)
    {
        $prioridades = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Entity::class)
            ->findBy([], [
                'peso' => 'ASC',
                'nome' => 'ASC',
            ]);

        return $this->render('admin/prioridades/index.html.twig', [
            'tab'         => 'prioridades',
            'prioridades' => $prioridades,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="admin_prioridades_new", methods={"GET", "POST"})
     * @Route("/{id}", name="admin_prioridades_edit", methods={"GET", "POST"})
     */
    public function form(Request $request, TranslatorInterface $translator, Entity $entity = null)
    {
        if (!$entity) {
            $entity = new Entity();
            $entity->setCor('#0091da');
        }

        $form = $this->createForm(EntityType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', $translator->trans('Local salvo com sucesso!'));

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
     * @Route("/{id}", name="admin_prioridades_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TranslatorInterface $translator, Entity $prioridade)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($prioridade);
            $em->flush();

            $this->addFlash('success', $translator->trans('Prioridade removida com sucesso!'));

            return $this->redirectToRoute('admin_prioridades_index');
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'A prioridade não pode ser removida porque está sendo utilizada.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $translator->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
