<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class QueueOrderingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('field', ChoiceType::class, [
                'label' => 'label.field',
                'required' => false,
                'placeholder' => '(nenhum)',
                'choices'  => [
                    'Data de agendamento' => 'dataAgendamento',
                    'Data de chegada' => 'dataChegada',
                    'Peso prioridade' => 'prioridade',
                    'Peso serviço' => 'servico',
                    'Peso serviço usuário' => 'servicoUsuario',
                    'Peso serviço unidade' => 'servicoUnidade',
                    'Balanceamento prioridade vs data chegada' => 'balanceamento',
                ],
            ])
            ->add('order', ChoiceType::class, [
                'label'   => 'label.order',
                'choices' => [
                    'Asc'  => 'ASC',
                    'Desc' => 'DESC',
                ],
            ])
        ;
    }
}
