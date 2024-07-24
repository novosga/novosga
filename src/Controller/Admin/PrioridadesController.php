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

use App\Entity\Prioridade as Entity;
use App\Form\PrioridadeType as EntityType;
use App\Repository\PrioridadeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PrioridadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/prioridades', name: 'admin_prioridades_')]
class PrioridadesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PrioridadeRepository $repository,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $prioridades = $this
            ->repository
            ->findBy([], [
                'peso' => 'ASC',
                'nome' => 'ASC',
            ]);

        return $this->render('admin/prioridades/index.html.twig', [
            'tab'         => 'prioridades',
            'prioridades' => $prioridades,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route('/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function form(Request $request, TranslatorInterface $translator, Entity $entity = null): Response
    {
        if (!$entity) {
            $entity = new Entity();
            $entity->setCor('#0091da');
        }

        $form = $this
            ->createForm(EntityType::class, $entity)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Local salvo com sucesso!'));

            return $this->redirectToRoute('admin_prioridades_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/prioridades/form.html.twig', [
            'tab'    => 'prioridades',
            'entity' => $entity,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, TranslatorInterface $translator, Entity $prioridade): Response
    {
        try {
            $this->em->remove($prioridade);
            $this->em->flush();

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
