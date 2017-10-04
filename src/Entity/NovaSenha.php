<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Novosga\Entity\Cliente;

/**
 * NovaSenha
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class NovaSenha
{
    /**
     * @var int
     */
    public $unidade;
    
    /**
     * @var int
     */
    public $prioridade;
    
    /**
     * @var int
     */
    public $servico;
    
    /**
     * @var Cliente
     */
    public $cliente;
    
    /**
     * @var mixed
     */
    public $metadata;
    
    public function __construct()
    {
        $this->cliente = new Cliente();
    }
}
