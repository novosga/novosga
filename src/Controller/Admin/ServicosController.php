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

use App\Form\ServicoType;
use App\Entity\Servico;
use App\Repository\ServicoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ServicosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/servicos', name: 'admin_servicos_')]
class ServicosController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ServicoRepository $repository,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $servicos = $this
            ->repository
            ->createQueryBuilder('e')
            ->where('e.deletedAt IS NULL')
            ->andWhere('e.mestre IS NULL')
            ->getQuery()
            ->getResult();

        return $this->render('admin/servicos/index.html.twig', [
            'tab' => 'servicos',
            'servicos' => $servicos,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route('/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function form(Request $request, TranslatorInterface $translator, Servico $entity = null): Response
    {
        if (!$entity) {
            $entity = new Servico();
        }

        $form = $this
            ->createForm(ServicoType::class, $entity)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Serviço salvo com sucesso!'));

            return $this->redirectToRoute('admin_servicos_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/servicos/form.html.twig', [
            'tab'    => 'servicos',
            'entity' => $entity,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, TranslatorInterface $translator, Servico $servico): Response
    {
        try {
            $this->em->remove($servico);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Serviço removido com sucesso!'));

            return $this->redirectToRoute('admin_servicos_index');
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'O serviço não pode ser removido porque está sendo utilizado.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $translator->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
