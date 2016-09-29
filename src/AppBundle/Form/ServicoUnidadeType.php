<?php

namespace AppBundle\Form;

use Novosga\Entity\ServicoUnidade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicoUnidadeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('horaInicio', TimeType::class)
            ->add('horaFim', TimeType::class)
            ->add('maximoAtendimentos', NumberType::class)
            ->add('tempoAtendimento', NumberType::class)
            ->add('numeroInicial', NumberType::class)
            ->add('numeroFinal', NumberType::class)
            ->add('incremento', NumberType::class)
            ->add('prioridade', CheckboxType::class)
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ServicoUnidade::class
        ));
    }
}
