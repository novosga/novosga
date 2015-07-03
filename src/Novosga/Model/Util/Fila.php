<?php

namespace Novosga\Model\Util;

use Novosga\Model\Model;
use Novosga\Model\Atendimento;
use Novosga\Util\Arrays;

/**
 * Lista dos atendimentos ao Servico da Unidade.
 */
class Fila extends Model
{
    private $atendimentos = array();

    public function __construct(array $atendimentos = array())
    {
        $this->setAtendimentos($atendimentos);
    }

    /**
     * @param array $atendimentos
     */
    private function setAtendimentos(array $atendimentos)
    {
        $this->atendimentos = $atendimentos;
    }

    /**
     * Retorna o Atendimento contido na posicao especifica da fila.
     *
     * @param int $i (index)
     *
     * @return Atendimento
     */
    public function get($i)
    {
        return $this->atendimentos[$i];
    }

    /**
     * Retorna todos Atendimentos da fila.
     *
     * @return array
     */
    public function getAtendimentos()
    {
        return $this->atendimentos;
    }

    /**
     * Define o Atendimento contido na posicao especifica da fila.
     *
     * @param int         $i           (index)
     * @param Atendimento $atendimento
     */
    public function set($i, Atendimento $atendimento)
    {
        $this->atendimentos[(int) $i] = $atendimento;
    }

    /**
     * Adiciona na fila um Atendimento.
     */
    public function add(Atendimento $atendimento)
    {
        $this->fila[] = $atendimento;
    }

    /**
     * Remove o Atendimento da posicao especifica da fila.
     *
     * @param int $i (index)
     *
     * @return bool
     */
    public function remove($i)
    {
        Arrays::removeKey($this->atendimentos, (int) $i);
    }

    /**
     * Retorna a quantidade de Atendimentos na fila.
     *
     * @return int
     */
    public function size()
    {
        return sizeof($this->atendimentos);
    }

    /**
     * Retorna se tem ou nao gente na fila.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return ($this->size() == 0);
    }

    /**
     * Retorna quantidade total da fila.
     *
     * @return String
     */
    public function toString()
    {
        return 'Fila[total:'.$this->size().']';
    }

    /**
     * Retorna resultado do método toString.
     *
     * @return String
     */
    public function __tostring()
    {
        return $this->toString();
    }
}
