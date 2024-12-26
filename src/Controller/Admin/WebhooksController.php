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
use App\Entity\Webhook as Entity;
use App\Form\WebhookType as EntityType;
use App\Repository\WebhookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * WebhooksController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/webhooks', name: 'admin_webhooks_')]
class WebhooksController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WebhookRepository $repository,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $webhooks = $this
            ->repository
            ->findBy([], ['name' => 'ASC']);

        return $this->render('admin/webhooks/index.html.twig', [
            'tab' => 'webhook',
            'webhooks' => $webhooks,
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
            // ignore empty headers
            $headers = array_filter(
                $entity->getHeaders(),
                fn ($value, $key) => !empty($key) && !empty($value),
                ARRAY_FILTER_USE_BOTH
            );
            $entity->setHeaders($headers);

            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Webhook salvo com sucesso!'));

            return $this->redirectToRoute('admin_webhooks_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/webhooks/form.html.twig', [
            'tab'    => 'webhooks',
            'entity' => $entity,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, TranslatorInterface $translator, Entity $webhook): Response
    {
        try {
            $this->em->remove($webhook);
            $this->em->flush();

            $this->addFlash('success', $translator->trans('Webhook removido com sucesso!'));

            return $this->redirectToRoute('admin_webhooks_index');
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
