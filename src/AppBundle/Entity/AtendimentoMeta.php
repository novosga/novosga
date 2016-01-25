<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
