<?php

namespace AppBundle\Form;

use AppBundle\Entity\Unidade;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class UnidadeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo', TextType::class, [
                'constraints' => [
                    new UniqueEntity("codigo"),
                    new UniqueEntity("nome"),
                    new NotBlank(),
                    new Length([ 'max' => 10 ]),
                ]
            ])
            ->add('nome', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([ 'max' => 50 ]),
                ]
            ])
            ->add('status', TextType::class, [
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add('statusImpressao', TextType::class, [
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add('mensagemImpressao', TextType::class, [
                'constraints' => [
                    new NotNull(),
                    new Length([ 'max' => 100 ]),
                ]
            ])
            ->add('grupo', EntityType::class, [
                'class' => \AppBundle\Entity\Grupo::class,
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add('contador')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Unidade::class
        ));
    }
}
