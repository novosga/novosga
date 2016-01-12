<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Classe Atendimento Codificado
 * representa o atendimento codificado (servico realizado).
 *
 * @ ORM\Entity
 * @ ORM\Table(name="atend_codif")
 */
class AtendimentoCodificado extends AbstractAtendimentoCodificado
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
