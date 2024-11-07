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

use App\Dto\ApplicationSettings;
use App\Form\Settings\AppearanceSettingsFormType;
use App\Form\Settings\BehaviorSettingsFormType;
use App\Form\Settings\QueueSettingsFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ApplicationSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('appearance', AppearanceSettingsFormType::class, [
                'constraints' => [
                    new Valid(),
                ],
            ])
            ->add('behavior', BehaviorSettingsFormType::class, [
                'constraints' => [
                    new Valid(),
                ],
            ])
            ->add('queue', QueueSettingsFormType::class, [
                'constraints' => [
                    new Valid(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicationSettings::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app';
    }
}
