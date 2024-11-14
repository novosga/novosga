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

namespace App\Dto;

use App\Entity\Cliente;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

/**
 * NovaSenha
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
final readonly class NovaSenha
{
    public function __construct(
        #[NotNull]
        #[Range(min: 1)]
        public ?int $unidade = null,
        #[NotNull]
        #[Range(min: 1)]
        public ?int $prioridade = null,
        #[NotNull]
        #[Range(min: 1)]
        public ?int $servico = null,
        public ?Cliente $cliente = null,
        public mixed $metadata = null,
    ) {
    }
}
