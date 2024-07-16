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

use App\Entity\Usuario;

/**
 * UserAwareTrait
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
trait UserAwareTrait
{
    /**
     * @var Usuario
     */
    private $user;
    
    /**
     * {@inheritdoc}
     */
    public function setUser(Usuario $user)
    {
        $this->user = $user;
        return $this;
    }
        
    /**
     * {@inheritdoc}
     */
    public function getUser(): Usuario
    {
        return $this->user;
    }
}
