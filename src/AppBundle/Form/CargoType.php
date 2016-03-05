<?php

namespace AppBundle\Form;

use Novosga\Entity\Cargo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CargoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nome')
            ->add('descricao', TextareaType::class, [
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('modulos', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, [
                'class' => \Novosga\Entity\Modulo::class,
                'multiple' => true,
                'expanded' => true
            ])
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Cargo::class
        ));
    }
}
