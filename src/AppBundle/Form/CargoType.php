<?php

namespace AppBundle\Form;

use Novosga\Entity\Cargo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CargoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modulos = [];
        dump($options['modulos']);
        foreach ($options['modulos'] as $modulo) {
            if ($modulo instanceof \Novosga\Module\ModuleInterface) {
                $modulos[$modulo->getDisplayName()] = $modulo->getKeyName();
            }
        }
        
        $builder
            ->add('nome')
            ->add('descricao', TextareaType::class, [
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('modulos', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices' => $modulos
            ])
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Cargo::class
            ])
            ->setRequired('modulos');
    }
}
