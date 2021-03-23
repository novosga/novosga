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

use App\Form\DepartamentoType as EntityType;
use Novosga\Entity\Departamento as Entity;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DepartamentoController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/departamentos")
 */
class DepartamentoController extends AbstractController
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_departamentos_index")
     */
    public function index(Request $request)
    {
        $departamentos = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Entity::class)
            ->findBy([], ['nome' => 'ASC']);

        return $this->render('admin/departamentos/index.html.twig', [
            'tab'           => 'departamentos',
            'departamentos' => $departamentos,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="admin_departamentos_new", methods={"GET", "POST"})
     * @Route("/{id}", name="admin_departamentos_edit", methods={"GET", "POST"})
     */
    public function form(Request $request, TranslatorInterface $translator, Entity $entity = null)
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

            $this->addFlash('success', $translator->trans('Departamento salvo com sucesso!'));

            return $this->redirectToRoute('admin_departamentos_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/departamentos/form.html.twig', [
            'tab'    => 'departamentos',
            'entity' => $entity,
            'form'   => $form->createView(),
        ]);
    }

    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/{id}", name="admin_departamentos_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TranslatorInterface $translator, Entity $departamento)
    {
        try {
            $em  = $this->getDoctrine()->getManager();
            $em->remove($departamento);
            $em->flush();

            $this->addFlash('success', $translator->trans('Departamento removido com sucesso!'));

            return $this->redirectToRoute('admin_departamentos_index');
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'O departamento não pode ser removido porque está sendo utilizado.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $translator->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
