<?php

namespace Novosga\Model;

/**
 * Classe Atendimento Codificado (Historico)
 * representa o atendimento codificado (servico realizado).
 *
 * @Entity
 * @Table(name="historico_atend_codif")
 */
class AtendimentoCodificadoHistorico extends AbstractAtendimentoCodificado
{
    /**
     * @Id
     * @ManyToOne(targetEntity="AtendimentoHistorico")
     * @JoinColumn(name="atendimento_id", referencedColumnName="id")
     *
     * @var AtendimentoHistorico
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
