<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Dto;

use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * EncerrarAtendimento
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
final readonly class EncerrarAtendimento
{
    /**
     * @param array<int> $servicosRealizados
     */
    public function __construct(
        #[Count(min: 1)]
        public array $servicosRealizados = [],
        public ?int $servicoRedirecionado = null,
        public ?int $novoUsuario = null,
    ) {
    }
}
