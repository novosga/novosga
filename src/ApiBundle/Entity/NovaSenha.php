<?php

namespace ApiBundle\Entity;

use Novosga\Entity\Cliente;

/**
 * NovaSenha
 *
 * @author rogerio
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