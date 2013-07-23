<?php
namespace core\model;

/**
 * Classe ViewAtendimento Codificado
 * representa a view de historico de atendimento codificado (servico realizado)
 * 
 * @Entity
 * @Table(name="view_historico_atend_codif")
 */
class ViewAtendimentoCodificado extends Model {

    /** 
     * @Id 
     * @ManyToOne(targetEntity="ViewAtendimento") 
     * @JoinColumn(name="id_atend", referencedColumnName="id_atend")
     */
    protected $atendimento;
    /** 
     * @Id 
     * @ManyToOne(targetEntity="Servico") 
     * @JoinColumn(name="id_serv", referencedColumnName="id_serv")
     */
    protected $servico;
    /** @Column(type="integer", name="peso", nullable=false) */
    protected $peso;
    
    public function getAtendimento() {
        return $this->atendimento;
    }

    public function setAtendimento($atendimento) {
        $this->atendimento = $atendimento;
    }

    public function getServico() {
        return $this->servico;
    }

    public function setServico($servico) {
        $this->servico = $servico;
    }

    public function getPeso() {
        return $this->peso;
    }

    public function setPeso($peso) {
        $this->peso = $peso;
    }

}
