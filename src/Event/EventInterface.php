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

namespace App\Event;

/**
 * Event
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
interface EventInterface
{
    /**
     * Returns event name
     * 
     * @return string
     */
    public function getName(): string;
    
    /**
     * Returns event data
     * 
     * @return mixed
     */
    public function getData();
}
