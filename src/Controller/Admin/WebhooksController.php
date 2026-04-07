<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
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
use App\Service\WebhookService;
use App\Webhook\PredefinedWebhookDefinition;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Attribute\Route;
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
        private readonly WebhookService $service,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $webhooks = $this->service->findAll();

        return $this->render('admin/webhooks/index.html.twig', [
            'tab' => 'webhook',
            'webhooks' => $webhooks,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET'])]
    public function select(): Response
    {
        return $this->render('admin/webhooks/select.html.twig', [
            'tab' => 'webhooks',
            'predefined' => PredefinedWebhookDefinition::all(),
        ]);
    }

    #[Route('/new/manual', name: 'new_manual', methods: ['GET', 'POST'])]
    public function newManual(Request $request, TranslatorInterface $translator): Response
    {
        $entity = new Entity();

        return $this->handleWebhookForm($request, $translator, $entity, 'admin_webhooks_new');
    }

    #[Route('/new/predefined/{key}', name: 'new_predefined', methods: ['GET', 'POST'])]
    public function newPredefined(Request $request, TranslatorInterface $translator, string $key): Response
    {
        $definition = PredefinedWebhookDefinition::find($key);
        if (!$definition) {
            throw $this->createNotFoundException();
        }

        $form = $this->createFormBuilder(['name' => $definition->name, 'accessToken' => '', 'enabled' => true])
            ->add('name', TextType::class, ['label' => 'label.name'])
            ->add('accessToken', TextType::class, ['label' => 'label.access_token'])
            ->add('enabled', CheckboxType::class, ['label' => 'label.enabled', 'required' => false])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $entity = (new Entity())
                ->setName($data['name'])
                ->setUrl($definition->url)
                ->setHeaders(['Authorization' => 'Bearer ' . $data['accessToken']])
                ->setEvents($definition->events)
                ->setEnabled($data['enabled']);

            $this->service->save($entity);

            $this->addFlash('success', $translator->trans('Webhook salvo com sucesso!'));

            return $this->redirectToRoute('admin_webhooks_edit', ['id' => $entity->getId()]);
        }

        return $this->render('admin/webhooks/predefined_form.html.twig', [
            'tab' => 'webhooks',
            'definition' => $definition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TranslatorInterface $translator, Entity $entity): Response
    {
        return $this->handleWebhookForm($request, $translator, $entity, 'admin_webhooks_index');
    }

    private function handleWebhookForm(
        Request $request,
        TranslatorInterface $translator,
        Entity $entity,
        string $backRoute,
    ): Response {
        $form = $this
            ->createForm(EntityType::class, $entity)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $headers = array_filter(
                $entity->getHeaders(),
                fn ($value, $key) => !empty($key) && !empty($value),
                ARRAY_FILTER_USE_BOTH
            );
            $entity->setHeaders($headers);

            $this->service->save($entity);

            $this->addFlash('success', $translator->trans('Webhook salvo com sucesso!'));

            return $this->redirectToRoute('admin_webhooks_edit', ['id' => $entity->getId()]);
        }

        return $this->render('admin/webhooks/form.html.twig', [
            'tab' => 'webhooks',
            'entity' => $entity,
            'form' => $form,
            'backRoute' => $backRoute,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, TranslatorInterface $translator, Entity $webhook): Response
    {
        try {
            $this->service->remove($webhook);

            $this->addFlash('success', $translator->trans('Webhook removido com sucesso!'));

            return $this->redirectToRoute('admin_webhooks_index');
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
