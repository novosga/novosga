<?php

namespace modules\sga\estatisticas;

/**
 * Grafico.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Grafico extends Relatorio
{
    private $legendas = [];

    public function __construct($titulo, $tipo, $opcoes = '')
    {
        parent::__construct($titulo, $tipo, $opcoes);
    }

    public function getLegendas()
    {
        return $this->legendas;
    }

    public function setLegendas($legendas)
    {
        $this->legendas = $legendas;
    }

    public function jsonSerialize()
    {
        return [
            'tipo'     => $this->arquivo,
            'titulo'   => $this->titulo,
            'dados'    => $this->dados,
            'legendas' => $this->legendas,
        ];
    }
}
