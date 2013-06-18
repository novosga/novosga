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
    protected $opcoes;


    public function __construct($titulo, $arquivo, $opcoes = '') {
        $this->titulo = $titulo;
        $this->arquivo = $arquivo;
        $this->opcoes = $opcoes;
        $this->dados = array();
    }
    
    public function getTitulo() {
        return $this->titulo;
    }
    
    public function getArquivo() {
        return $this->arquivo;
    }
    public function getOpcoes() {
        return $this->opcoes;
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
