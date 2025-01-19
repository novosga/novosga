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

use Novosga\Settings\QueueSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Valid;

class QueueSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ordering', CollectionType::class, [
                'label' => 'label.ordering',
                'required' => true,
                'entry_type' => QueueOrderingFormType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'constraints' => [
                    new Valid(),
                    new Count(exactly: 5),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QueueSettings::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_queue';
    }
}
