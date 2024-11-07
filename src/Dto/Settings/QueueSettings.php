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

namespace App\Dto\Settings;

class QueueSettings
{
    /** @param array<array<string,string>> $ordering */
    public function __construct(
        public array $ordering = [],
    ) {
        if (!count($this->ordering)) {
            $this->ordering = [
                [
                    'field' => 'dataAgendamento',
                    'order' => 'ASC',
                ],
                [
                    'field' => 'servicoUsuario',
                    'order' => 'ASC',
                ],
                [
                    'field' => 'prioridade',
                    'order' => 'DESC',
                ],
                [
                    'field' => 'servicoUnidade',
                    'order' => 'DESC',
                ],
                [
                    'field' => 'dataChegada',
                    'order' => 'ASC',
                ],
            ];
        }
    }
}
