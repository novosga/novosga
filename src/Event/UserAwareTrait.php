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

use Novosga\Entity\UsuarioInterface;

/**
 * UserAwareTrait
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
trait UserAwareTrait
{
    private ?UsuarioInterface $user = null;

    public function setUser(?UsuarioInterface $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?UsuarioInterface
    {
        return $this->user;
    }
}
