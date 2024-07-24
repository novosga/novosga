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

use Novosga\Infrastructure\StorageInterface;

/**
 * StorageAwareTrait
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
trait StorageAwareTrait
{
    private ?StorageInterface $storage = null;

    public function setStorage(?StorageInterface $storage): static
    {
        $this->storage = $storage;

        return $this;
    }

    public function getStorage(): ?StorageInterface
    {
        return $this->storage;
    }
}
