<?php

namespace Novosga\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AtendimentoHistorico
 * historico de atendimento do banco de dados.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoHistorico extends AbstractAtendimento
{
    /**
     * @var AtendimentoCodificadoHistorico[]
     */
    private $codificados;

    public function __construct()
    {
        $this->codificados = new ArrayCollection();
    }

    public function getCodificados()
    {
        return $this->codificados;
    }

    public function setCodificados(Collection $codificados)
    {
        $this->codificados = $codificados;

        return $this;
    }
}
