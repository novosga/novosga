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

use Novosga\Settings\AppearanceSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class AppearanceSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme', ChoiceType::class, [
                'label' => 'label.theme',
                'choices' => [
                    'Default' => 'default',
                    'Cerulean' => 'cerulean',
                    'Cosmo' => 'cosmo',
                    'Cyborg' => 'cyborg',
                    'Darkly' => 'darkly',
                    'Flatly' => 'flatly',
                    'Journal' => 'journal',
                    'Litera' => 'litera',
                    'Lumen' => 'lumen',
                    'Lux' => 'lux',
                    'Materia' => 'materia',
                    'Minty' => 'minty',
                    'Morph' => 'morph',
                    'Pulse' => 'pulse',
                    'Quartz' => 'quartz',
                    'Sandstone' => 'sandstone',
                    'Simplex' => 'simplex',
                    'Sketchy' => 'sketchy',
                    'Slate' => 'slate',
                    'Solar' => 'solar',
                    'Spacelab' => 'spacelab',
                    'Superhero' => 'superhero',
                    'United' => 'united',
                    'Vapor' => 'vapor',
                    'Yeti' => 'yeti',
                    'Zephyr' => 'zephyr',
                ]
            ])
            ->add('navbarColor', ChoiceType::class, [
                'label' => 'label.navbar',
                'choices' => [
                    'Light' => 'bg-bg-light',
                    'Dark' => 'bg-dark',
                    'Primary' => 'bg-primary',
                    'Tertiary' => 'bg-body-tertiary',
                ]
            ])
            ->add('logoNavbar', FileType::class, [
                'label'    => 'label.logo_navbar',
                'required' => false,
                'mapped'   => false,
                'constraints' => [
                    new Image(),
                ],
                'attr' => [
                    'accept' => 'image/*',
                ],
            ])
            ->add('logoLogin', FileType::class, [
                'label'    => 'label.logo_login',
                'required' => false,
                'mapped'   => false,
                'constraints' => [
                    new Image(),
                ],
                'attr' => [
                    'accept' => 'image/*',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppearanceSettings::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_appearance';
    }
}
