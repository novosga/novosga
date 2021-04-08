<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use Novosga\Entity\Endereco;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;

class EnderecoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Endereco::class
        ));
    }
}
