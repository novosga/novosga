<?php

namespace Novosga\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AtendimentoHistorico
 * historico de atendimento do banco de dados.
 *
 * @Entity
 * @Table(name="historico_atendimentos")
 */
class AtendimentoHistorico extends AbstractAtendimento
{
    /**
     * @OneToMany(targetEntity="AtendimentoCodificadoHistorico", mappedBy="atendimento")
     *
     * @var AtendimentoCodificadoHistorico[]
     */
    protected $codificados;

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
