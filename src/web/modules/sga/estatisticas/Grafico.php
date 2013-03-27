<?php
namespace modules\sga\estatisticas;

/**
 * Grafico
 *
 * @author rogeriolino
 */
class Grafico extends Relatorio {
    
    private $tipo;
    private $legendas = array();
    
    public function __construct($titulo, $tipo) {
        parent::__construct($titulo, '');
        $this->tipo = $tipo;
    }
    
    public function getLegendas() {
        return $this->legendas;
    }

    public function setLegendas($legendas) {
        $this->legendas = $legendas;
    }

    public function toArray() {
        return array(
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'dados' => $this->dados,
            'legendas' => $this->legendas
        );
    }

}
