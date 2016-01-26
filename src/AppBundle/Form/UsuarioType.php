<?php

namespace AppBundle\Form;

use Novosga\Entity\Usuario;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class UsuarioType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['entity'];
        
        $builder
            ->add('login', TextType::class, [
                'label' => 'Nome de usuário',
                'attr' => [
                    'onkeyup' => 'SGA.Form.loginValue(this)'
                ],
                'constraints' => [
                    new UniqueEntity("login"),
                    new NotBlank(),
                    new Length([ 'min' => 5, 'max' => 20 ]),
                    new Regex("/^[a-zA-Z0-9\.]+$/"),
                ]
            ])
            ->add('nome', TextType::class, [
                'label' => 'Nome',
                'constraints' => [
                    new NotBlank(),
                    new Length([ 'max' => 20 ]),
                ]
            ])
            ->add('sobrenome', TextType::class, [
                'label' => 'Sobrenome',
                'constraints' => [
                    new NotBlank(),
                    new Length([ 'max' => 100 ]),
                ]
            ])
            ->add('lotacoes', CollectionType::class, [
                'entry_type' => LotacaoType::class,
                'constraints' => [
                    new Count([ 'min' => 1 ]),
                ]
            ])
        ;
        
        if ($entity->getId()) {
            $builder->add('status', CheckboxType::class, [
                'label' => 'Status',
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ]
            ]);
        } else {
            $builder
                ->add('senha', PasswordType::class, [
                    'label' => 'Senha',
                'constraints' => [
                    
                ]
                ])
                ->add('confirmacaoSenha', PasswordType::class, [
                    'label' => 'Confirmação da senha',
                    'mapped' => false,
                    'constraints' => [

                    ]
                ]);
            
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();
                $confirmacao = $form->getConfirmacaoSenha();
                
                if ($entity->getSenha() !== $confirmacao->getData()) {
                    $confirmacao->addError(new FormError('A confirmação de senha não confere com a senha.'));
                }
            });
        }
    }
    
    /**
     * 
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class
        ])->setRequired([
            'entity'
        ])->setAllowedTypes('entity', Usuario::class);
    }
}
