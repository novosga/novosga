<?php

namespace Novosga\Model;

/**
 * AtendimentoMeta.
 *
 * @Entity
 * @Table(name="atend_meta")
 */
class AtendimentoMeta extends AbstractAtendimentoMeta
{
    /**
     * @Id
     * @ManyToOne(targetEntity="Atendimento")
     * @JoinColumn(name="atendimento_id", referencedColumnName="id")
     *
     * @var Atendimento
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
