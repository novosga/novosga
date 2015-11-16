<?php

namespace Novosga\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe ViewAtendimento
 * representa a view de historico de atendimento do banco de dados.
 *
 * @Entity
 * @Table(name="view_historico_atendimentos")
 */
class ViewAtendimento extends AbstractAtendimento
{
    /**
     * @OneToMany(targetEntity="ViewAtendimentoCodificado", mappedBy="atendimento")
     *
     * @var ViewAtendimentoCodificado[]
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
