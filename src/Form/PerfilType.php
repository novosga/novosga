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

use Novosga\Entity\Perfil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use function ksort;

class PerfilType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modulos = [];
        
        foreach ($options['modulos'] as $modulo) {
            if ($modulo instanceof \Novosga\Module\ModuleInterface) {
                $key    = $modulo->getKeyName();
                $name   = $this
                    ->translator
                    ->trans(
                        $modulo->getDisplayName(),
                        [],
                        $modulo->getName()
                    );

                $name .= " ({$key})";

                $modulos[$name] = $key;
            }
        }

        ksort($modulos);
        
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
            ->add('modulos', ChoiceType::class, [
                'label' => 'admin.roles.field.modules',
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
                'data_class' => Perfil::class
            ])
            ->setRequired('modulos');
    }
}
