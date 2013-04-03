<?php
namespace core\model\util;

use \core\model\Model;
use \core\model\Prioridade;

/**
 * Classe Senha
 * Responsavel pelas informacoes do Senha
 *
 */
 class Senha extends Model {

     const LENGTH = 4;

    private $sigla;
    private $numero;
    private $prioridade;
    private $legenda;

    public function __construct() {
        $this->setLegenda(_('Senha|Bilhete'));
    }

    /**
     * Define a sigla da senha
     * @param char $sigla
     */
    public function setSigla($sigla) {
        if (is_string($sigla) && strlen($sigla) == 1) {
            $this->sigla = $sigla;
        } else {
            throw new \Exception(_('A sigla da senha deve ser um char'));
        }
    }

    /**
     * Retorna a sigla da senha
     * @return char $sigla
     */
    public function getSigla() {
        return $this->sigla;
    }

    /**
     * Define o numero da senha
     * @param int $numero
     */
    public function setNumero($numero) {
        if (is_int($numero) && $numero > 0) {
            $this->numero = $numero;
        } else {
            throw new \Exception(_("O numero da senha deve ser um inteiro maior que zero"));
        }
    }

    /**
     * Retorna o numero da senha
     * @return int $numero
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * Retorna o numero da senha preenchendo com zero (esquerda).
     * @return String
     */
    public function getNumeroZeros() {
        return str_pad($this->getNumero(), Senha::LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Define a Prioridade da senha
     * @param Prioridade $pri
     */
    public function setPrioridade($pri) {
        $this->prioridade = $pri;
    }

    /**
     * Retorna a Prioridade da Senha
     * @return Prioridade
     */
    public function getPrioridade() {
        return $this->prioridade;
    }

    /**
     * Define a legenda da senha
     * @param String $legenda
     */
    public function setLegenda($legenda) {
        if (is_string($legenda)) {
            $this->legenda = $legenda;
        } else {
            throw new Exception(_('A legenda da senha deve ser uma String'));
        }
    }

    /**
     * Retorna a legenda da senha
     * @return String
     */
    public function getLegenda() {
        return $this->legenda;
    }

    /**
     * Retorna se a senha tem ou nao prioridade
     * @return bool
     */
    public function isPrioridade() {
        return ($this->getPrioridade()->getPeso() > 0) ? true : false;
    }

    /**
     * Retorna a senha formatada para exibicao
     * @return String
     */
    public function toString() {
        return $this->getSigla() . $this->getNumeroZeros();
    }

    /**
     * Retorna resultado do método toString
     * @return String
     */
    public function __tostring() {
        return $this->toString();
    }

}
