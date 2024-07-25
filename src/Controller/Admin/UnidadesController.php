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

use Exception;
use App\Entity\Unidade as Entity;
use App\Form\UnidadeType as EntityType;
use App\Repository\UnidadeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UnidadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/unidades', name: 'admin_unidades_')]
class UnidadesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UnidadeRepository $repository,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $unidades = $this
            ->repository
            ->findBy([], ['nome' => 'ASC']);

        return $this->render('admin/unidades/index.html.twig', [
            'tab'      => 'unidades',
            'unidades' => $unidades,
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
            if (!$entity->getId()) {
                $entity->getImpressao()->setCabecalho('Novo SGA');
                $entity->getImpressao()->setRodape('========');
            }

            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Unidade salva com sucesso!'));

            return $this->redirectToRoute('admin_unidades_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/unidades/form.html.twig', [
            'tab'    => 'unidades',
            'entity' => $entity,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, TranslatorInterface $translator, Entity $unidade): Response
    {
        try {
            $this->em->remove($unidade);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Unidade removida com sucesso!'));

            return $this->redirectToRoute('admin_unidades_index');
        } catch (Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'A unidade não pode ser removida porque está sendo utilizada.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $translator->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
