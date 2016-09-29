<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Servico;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];
        
        $builder
            ->add('nome', TextType::class)
            ->add('descricao', TextareaType::class, [
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('status', CheckboxType::class, [
                'required' => false
            ])
            ->add('peso', NumberType::class)
        ;
        
        if (!$entity->isMestre()) {
            $builder->add('mestre', EntityType::class, [
                'class' => Servico::class,
                'placeholder' => 'Nenhum',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                                ->where('e.mestre IS NULL');
                }
            ]);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Servico::class
        ]);
    }
}
