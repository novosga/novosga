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

class BehaviorSettings
{
    public function __construct(
        public bool $prioritySwap = false,
        public string $prioritySwapMethod = 'unity',
        public int $prioritySwapCount = 1,
    ) {
    }
}
