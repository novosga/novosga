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

use App\Form\LocalType as EntityType;
use Novosga\Entity\Local as Entity;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * LocaisController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/locais")
 */
class LocaisController extends Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_locais_index")
     */
    public function index(Request $request)
    {
        $locais = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Entity::class)
            ->findBy([], ['nome' => 'ASC']);

        return $this->render('admin/locais/index.html.twig', [
            'tab'    => 'locais',
            'locais' => $locais,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="admin_locais_new", methods={"GET", "POST"})
     * @Route("/{id}", name="admin_locais_edit", methods={"GET", "POST"})
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

            $this->addFlash('success', $translator->trans('Local salvo com sucesso!'));

            return $this->redirectToRoute('admin_locais_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/locais/form.html.twig', [
            'tab'    => 'locais',
            'entity' => $entity,
            'form'   => $form->createView(),
        ]);
    }

    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/{id}", name="admin_locais_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TranslatorInterface $translator, Entity $local)
    {
        try {
            $em  = $this->getDoctrine()->getManager();
            $em->remove($local);
            $em->flush();

            $this->addFlash('success', $translator->trans('Local removido com sucesso!'));

            return $this->redirectToRoute('admin_locais_index');
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'O local não pode ser removido porque está sendo utilizado.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $translator->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
