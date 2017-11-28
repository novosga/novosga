<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Api;

use App\Form\ClienteType;
use Novosga\Entity\Agendamento;
use Novosga\Entity\Servico;
use Novosga\Entity\Unidade;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;

class AgendamentoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('data', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('hora', TimeType::class, [
                'widget' => 'single_text',
                'with_seconds' => false,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('cliente', ClienteType::class, [
                'constraints' => [
                    new Valid(),
                ],
            ])
            ->add('unidade', EntityType::class, [
                'class' => Unidade::class,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('servico', EntityType::class, [
                'class' => Servico::class,
                'constraints' => [
                    new NotNull(),
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
            'data_class' => Agendamento::class
        ));
    }
    
    public function getBlockPrefix()
    {
        return null;
    }
}
