<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Classe Atendimento Codificado (Historico)
 * representa o atendimento codificado (servico realizado).
 *
 * @ ORM\Entity
 * @ ORM\Table(name="historico_atend_codif")
 */
class AtendimentoCodificadoHistorico extends AbstractAtendimentoCodificado
{
    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="AtendimentoHistorico")
     * @ ORM\JoinColumn(name="atendimento_id", referencedColumnName="id")
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
