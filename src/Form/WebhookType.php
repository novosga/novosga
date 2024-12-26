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

namespace App\Form;

use App\Entity\Webhook;
use App\Types\WebhookEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * WebhookType
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebhookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $eventChoices = [];
        foreach (WebhookEvent::cases() as $event) {
            $eventChoices[$event->value] = $event->value;
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name',
                'constraints' => [
                    new Length(min: 1, max: 80),
                ],
            ])
            ->add('url', UrlType::class, [
                'label' => 'label.url',
            ])
            ->add('headers', HiddenType::class, [
                'label' => 'label.http_headers',
            ])
            ->add('events', ChoiceType::class, [
                'label' => 'label.events',
                'choices' => $eventChoices,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'label.enabled',
                'required' => false
            ])
        ;

        $builder->get('headers')->addModelTransformer(new CallbackTransformer(
            function ($headers) {
                return json_encode($headers);
            },
            function ($headers) {
                return json_decode($headers, true);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Webhook::class,
        ]);
    }
}
