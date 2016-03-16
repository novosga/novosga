<?php

namespace Novosga\Entity;

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
