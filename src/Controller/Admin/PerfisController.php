<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Form\PerfilType as EntityType;
use App\Entity\Perfil as Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PerfisController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route("/admin/perfis", name: "admin_perfis_")]
class PerfisController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route("/", name: "index")]
    public function index(Request $request): Response
    {
        $perfis = $this
            ->em
            ->createQueryBuilder()
            ->select('e')
            ->from(Entity::class, 'e')
            ->getQuery()
            ->getResult();

        return $this->render('admin/perfis/index.html.twig', [
            'tab'    => 'perfis',
            'perfis' => $perfis
        ]);
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    #[Route("/{id}", name: "edit", methods: ["GET", "POST"])]
    public function form(
        Request $request,
        TranslatorInterface $translator,
        Entity $entity = null
    ): Response {
        if (!$entity) {
            $entity = new Entity;
        }

        // TODO
        $modulos = [];

        $form = $this
            ->createForm(EntityType::class, $entity, [
                'modulos' => $modulos,
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Perfil salvo com sucesso!'));

            return $this->redirectToRoute('admin_perfis_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/perfis/form.html.twig', [
            'tab'    => 'perfis',
            'entity' => $entity,
            'form'   => $form,
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["DELETE"])]
    public function delete(Request $request, TranslatorInterface $translator, Entity $perfil): Response
    {
        try {
            $this->em->remove($perfil);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Perfil removido com sucesso!'));

            return $this->redirectToRoute('admin_perfis_index');
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'O perfil não pode ser removido porque está sendo utilizado.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $translator->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
