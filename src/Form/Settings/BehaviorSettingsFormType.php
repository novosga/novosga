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

namespace App\Form\Settings;

use Novosga\Settings\BehaviorSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class BehaviorSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prioritySwap', CheckboxType::class, [
                'label' => 'label.priority_swap',
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('prioritySwapMethod', ChoiceType::class, [
                'label' => 'label.priority_swap_method',
                'choices' => [
                    'label.priority_swap_method_user' => 'user',
                    'label.priority_swap_method_unity' => 'unity',
                ],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('prioritySwapCount', IntegerType::class, [
                'label' => 'label.priority_swap_count',
                'constraints' => [
                    new NotNull(),
                    new Range(min: 1, max: 10),
                ],
            ])
            ->add('callTicketByService', CheckboxType::class, [
                'label' => 'label.call_ticket_by_service',
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('callTicketOutOfOrder', CheckboxType::class, [
                'label' => 'label.call_ticket_out_of_order',
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BehaviorSettings::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_behavior';
    }
}
