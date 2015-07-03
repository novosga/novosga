<?php

namespace Novosga\Model;

/**
 * AtendimentoMeta (Historico).
 *
 * @Entity
 * @Table(name="historico_atend_meta")
 */
class AtendimentoHistoricoMeta extends AbstractAtendimentoMeta
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
