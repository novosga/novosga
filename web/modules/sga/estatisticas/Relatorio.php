<?php
namespace modules\sga\estatisticas;

/**
 * Relatorio
 *
 * @author rogeriolino
 */
class Relatorio {
    
    protected $titulo;
    protected $dados;
    protected $arquivo;
    
    public function __construct($titulo, $arquivo) {
        $this->titulo = $titulo;
        $this->arquivo = $arquivo;
        $this->dados = array();
    }
    
    public function getTitulo() {
        return $this->titulo;
    }
    
    public function getArquivo() {
        return $this->arquivo;
    }

    public function getDados() {
        return $this->dados;
    }

    public function setDados($dados) {
        $this->dados = $dados;
    }
    
    public function toArray() {
        return array(
            'titulo' => $this->titulo,
            'dados' => $this->dados,
        );
    }

}
