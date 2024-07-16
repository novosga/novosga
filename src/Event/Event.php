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
class Event implements EventInterface
{
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var mixed
     */
    private $data;
    
    public function __construct(string $name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }
}
