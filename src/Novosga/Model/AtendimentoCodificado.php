<?php

namespace Novosga\Model;

/**
 * Classe Atendimento Codificado
 * representa o atendimento codificado (servico realizado).
 *
 * @Entity
 * @Table(name="atend_codif")
 */
class AtendimentoCodificado extends AbstractAtendimentoCodificado
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
