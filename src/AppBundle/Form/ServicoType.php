<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Novosga\Entity\Servico;

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
            ->add('nome')
            ->add('descricao', TextareaType::class, [
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('status')
            ->add('peso')
        ;
        
        if (count($entity->getSubServicos()) === 0) {
            $builder->add('mestre', EntityType::class, [
                'class' => Servico::class,
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                                ->where('e.mestre IS NULL');
                }
            ]);
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Novosga\Entity\Servico::class
        ));
    }
}
