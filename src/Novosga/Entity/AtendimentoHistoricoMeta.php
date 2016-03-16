<?php

namespace Novosga\Entity;

/**
 * AtendimentoMeta (Historico).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoHistoricoMeta extends AbstractAtendimentoMeta
{
    /**
     * @var AtendimentoHistorico
     */
    private $atendimento;

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
