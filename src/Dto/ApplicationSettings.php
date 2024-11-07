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

use App\Dto\Settings\AppearanceSettings;
use App\Dto\Settings\BehaviorSettings;
use App\Dto\Settings\QueueSettings;

class ApplicationSettings
{
    public function __construct(
        public AppearanceSettings $appearance,
        public BehaviorSettings $behavior,
        public QueueSettings $queue,
    ) {
    }
}
