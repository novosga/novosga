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

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Servico;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ->add('nome', TextType::class, [
                'label' => 'label.name',
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'label.description',
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('ativo', CheckboxType::class, [
                'label' => 'label.enabled',
                'required' => false
            ])
            ->add('peso', IntegerType::class, [
                'label' => 'label.weight',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Range([ 'min' => 0 ]),
                ]
            ])
        ;

        if (!$entity->isMestre() || count($entity->getSubservicos()) === 0) {
            $builder->add('mestre', EntityType::class, [
                'label' => 'admin.services.field.parent',
                'class' => Servico::class,
                'placeholder' => 'Nenhum',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($entity) {
                    $qb = $er
                        ->createQueryBuilder('e')
                        ->where('e.mestre IS NULL');

                    if ($entity->getId()) {
                        $qb
                            ->andWhere('e != :entity')
                            ->setParameter('entity', $entity);
                    }

                    return $qb;
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
