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

use Psr\Log\LoggerInterface;

/**
 * LoggerAwareTrait
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
trait LoggerAwareTrait
{
    /**
     * @var LoggerInterface
     */
    private $storage;
    
    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }
        
    /**
     * {@inheritdoc}
     */
    public function getLogger(): LoggerInterface
    {
        return $this->storage;
    }
}
