<?php

namespace AppBundle\Entity\Util;

use AppBundle\Entity\Model;

/**
 * Classe auxiliar.
 *
 * @author rogerio
 */
class Cliente extends Model
{
    private $nome;
    private $documento;

    public function __construct()
    {
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setDocumento($documento)
    {
        $this->documento = $documento;
    }

    public function getDocumento()
    {
        return $this->documento;
    }

    public function toString()
    {
        return $this->getNome();
    }
}
