<?php

namespace modules\sga\estatisticas;

/**
 * Relatorio.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Relatorio implements \JsonSerializable
{
    protected $titulo;
    protected $dados;
    protected $arquivo;
    protected $opcoes;

    public function __construct($titulo, $arquivo, $opcoes = '')
    {
        $this->titulo = $titulo;
        $this->arquivo = $arquivo;
        $this->opcoes = $opcoes;
        $this->dados = array();
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    public function getArquivo()
    {
        return $this->arquivo;
    }
    public function getOpcoes()
    {
        return $this->opcoes;
    }

    public function getDados()
    {
        return $this->dados;
    }

    public function setDados($dados)
    {
        $this->dados = $dados;
    }

    public function jsonSerialize()
    {
        return array(
            'titulo' => $this->titulo,
            'dados' => $this->dados,
        );
    }
}
