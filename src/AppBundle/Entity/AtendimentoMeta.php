<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AtendimentoMeta.
 *
 * @ ORM\Entity
 * @ ORM\Table(name="atend_meta")
 */
class AtendimentoMeta extends AbstractAtendimentoMeta
{
    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Atendimento")
     * @ ORM\JoinColumn(name="atendimento_id", referencedColumnName="id")
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
