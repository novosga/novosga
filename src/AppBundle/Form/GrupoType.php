<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Grupo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrupoType extends AbstractType
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
                'class' => Grupo::class,
                'query_builder' => function (EntityRepository $er) use ($entity) {
                    $id = $entity && $entity->getId() ? $entity->getId() : 0;
                
                    return $er
                            ->createQueryBuilder('e')
                            ->where('e.id != :self')
                            ->orderBy('e.level', 'ASC')
                            ->addOrderBy('e.level', 'ASC')
                            ->setParameter('self', $id);
                }
            ])
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Grupo::class
        ));
    }
}
