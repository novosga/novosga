<?php

namespace Novosga\Model;

/**
 * Classe ViewAtendimento Codificado
 * representa a view de historico de atendimento codificado (servico realizado).
 *
 * @Entity
 * @Table(name="view_historico_atend_codif")
 */
class ViewAtendimentoCodificado extends AbstractAtendimentoCodificado
{
    /**
     * @Id
     * @ManyToOne(targetEntity="ViewAtendimento")
     * @JoinColumn(name="atendimento_id", referencedColumnName="id")
     */
    protected $atendimento;

    public function getAtendimento()
    {
        return $this->atendimento;
    }

    public function setAtendimento(AbstractAtendimento $atendimento)
    {
        $this->atendimento = $atendimento;

        return $this;
    }
}
