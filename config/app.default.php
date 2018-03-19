<?php

/*
 * Default Novo SGA configuration, please don't edit this file. 
 * For custom configuration make a copy named app.php in the same directory
 */

return [
    'queue' => [
        /**
         * Queue ordering
         * @param \Novosga\Entity\Unidade $unidade
         * @param \Novosga\Entity\Usuario $usuario (optional)
         * @return array
         */
        'ordering' =>  function (\Novosga\Configuration\OrderingParameter $param) {
            $ordering = [
                [
                    'exp' => 'atendimento.dataAgendamento',
                    'order' => 'ASC',
                ]
            ];
            
            if ($param->getUsuario()) {
                // peso servico x usuario
                $ordering[] = [
                    'exp' => 'servicoUsuario.peso',
                    'order' => 'DESC',
                ];
            }
    
            return array_merge($ordering, [
                // priority
                [
                    'exp'   => 'prioridade.peso',
                    'order' => 'DESC',
                ],
                // peso servico x unidade
                [
                    'exp'   => 'servicoUnidade.peso',
                    'order' => 'DESC',
                ],
                // dataChegada
                [
                    'exp'   => 'atendimento.dataChegada',
                    'order' => 'ASC',
                ]
            ]);
        },
    ]
];
