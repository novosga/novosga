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

namespace App\Form;

use App\Entity\Prioridade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class PrioridadeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];

        $builder
            ->add('nome', TextType::class, [
                'label' => 'label.name',
                'constraints' => [
                    new NotNull(),
                    new Length(min: 1, max: 64),
                ],
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'label.description',
                'attr' => [
                    'rows' => 4
                ],
                'constraints' => [
                    new NotNull(),
                    new Length(min: 1, max: 100),
                ],
            ])
            ->add('ativo', CheckboxType::class, [
                'label' => 'label.enabled',
                'required' => false
            ])
            ->add('peso', IntegerType::class, [
                'label' => 'label.weight',
                'disabled' => $data && $data->getId() === 1,
                'constraints' => [
                    new NotNull(),
                    new Range(min: 0),
                ],
            ])
            ->add('cor', ColorType::class, [
                'label' => 'label.color',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prioridade::class,
        ]);
    }
}
