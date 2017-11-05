<?php

/*
 * Default Novo SGA configuration, please don't edit this file. 
 * For custom configuration make a copy named app.php in the same directory
 */

return [
    'queue' => [
        'ordering' =>  [
            // priority
            [
                'exp'   => 'prioridade.peso',
                'order' => 'DESC',
            ],
            // peso servico x usuario
            [
                'exp'   => 'servicoUsuario.peso',
                'order' => 'ASC',
            ],
            // ticket number
            [
                'exp'   => 'atendimento.senha.numero',
                'order' => 'ASC',
            ]
        ]
    ]
];
