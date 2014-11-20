<?php
namespace Novosga\Model;

/**
 * ViewAtendimentoMeta
 * 
 * @Entity
 * @Table(name="view_historico_atend_meta")
 */
class ViewAtendimentoCodificado extends AbstractAtendimentoMeta
{
    
    /** 
     * @Id 
     * @ManyToOne(targetEntity="ViewAtendimento") 
     * @JoinColumn(name="atendimento_id", referencedColumnName="id")
     */
    protected $atendimento;
    
    public function getAtendimento() {
        return $this->atendimento;
    }

    public function setAtendimento($atendimento) {
        $this->atendimento = $atendimento;
        return $this;
    }

}
