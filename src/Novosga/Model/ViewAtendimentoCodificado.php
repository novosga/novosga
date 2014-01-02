<?php
namespace Novosga\Model;

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
     * @JoinColumn(name="atendimento_id", referencedColumnName="id")
     */
    protected $atendimento;
    /** 
     * @Id 
     * @ManyToOne(targetEntity="Servico") 
     * @JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;
    /** @Column(type="smallint", name="peso", nullable=false) */
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
