<?php

namespace AppBundle\Entity;

/**
 * AtendimentoMeta.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoMeta extends AbstractAtendimentoMeta
{
    /**
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
