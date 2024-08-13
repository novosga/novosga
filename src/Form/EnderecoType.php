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

use App\Entity\Endereco;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;

class EnderecoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pais', CountryType::class, [
                'label' => 'label.endereco.pais',
            ])
            ->add('cep', TextType::class, [
                'label' => 'label.endereco.cep',
                'constraints' => [
                    new Length([ 'max' => 25 ]),
                ],
            ])
            ->add('estado', TextType::class, [
                'label' => 'label.endereco.estado',
                'constraints' => [
                    new Length([ 'max' => 3 ]),
                ],
            ])
            ->add('cidade', TextType::class, [
                'label' => 'label.endereco.cidade',
                'constraints' => [
                    new Length([ 'max' => 30 ]),
                ],
            ])
            ->add('logradouro', TextType::class, [
                'label' => 'label.endereco.logradouro',
                'constraints' => [
                    new Length([ 'max' => 60 ]),
                ],
            ])
            ->add('numero', TextType::class, [
                'label' => 'label.endereco.numero',
                'constraints' => [
                    new Length([ 'max' => 10 ]),
                ],
            ])
            ->add('complemento', TextType::class, [
                'label' => 'label.endereco.complemento',
                'constraints' => [
                    new NotNull(),
                    new Length([ 'max' => 15 ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Endereco::class
        ));
    }
}
