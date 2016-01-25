<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
