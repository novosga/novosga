<?php

namespace AppBundle\Form;

use AppBundle\Entity\Cargo;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        $entity = $options['data'];
        
        $builder
            ->add('nome')
            ->add('descricao', TextareaType::class, [
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => Cargo::class,
                'query_builder' => function (EntityRepository $er) use ($entity) {
                    return $er
                            ->createQueryBuilder('e')
                            ->where('e.id != :self')
                            ->orderBy('e.level', 'ASC')
                            ->addOrderBy('e.level', 'ASC')
                            ->setParameter('self', $entity);
                }
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
