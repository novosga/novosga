<?php

namespace AppBundle\Entity;

/**
 * Classe Atendimento Codificado (Historico)
 * representa o atendimento codificado (servico realizado).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoCodificadoHistorico extends AbstractAtendimentoCodificado
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
