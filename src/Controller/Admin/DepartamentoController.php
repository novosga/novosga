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

use App\Form\DepartamentoType as EntityType;
use App\Entity\Departamento as Entity;
use App\Repository\DepartamentoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DepartamentoController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/departamentos', name: 'admin_departamentos_')]
class DepartamentoController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DepartamentoRepository $repository,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $departamentos = $this
            ->repository
            ->findBy([], ['nome' => 'ASC']);

        return $this->render('admin/departamentos/index.html.twig', [
            'tab'           => 'departamentos',
            'departamentos' => $departamentos,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route('/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function form(Request $request, TranslatorInterface $translator, Entity $entity = null): Response
    {
        if (!$entity) {
            $entity = new Entity();
        }

        $form = $this
            ->createForm(EntityType::class, $entity)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Departamento salvo com sucesso!'));

            return $this->redirectToRoute('admin_departamentos_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/departamentos/form.html.twig', [
            'tab'    => 'departamentos',
            'entity' => $entity,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, TranslatorInterface $translator, Entity $departamento): Response
    {
        try {
            $this->em->remove($departamento);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Departamento removido com sucesso!'));

            return $this->redirectToRoute('admin_departamentos_index');
        } catch (\Exception $e) {
            if ($e instanceof ForeignKeyConstraintViolationException) {
                $message = 'O departamento não pode ser removido porque está sendo utilizado.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $translator->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
