<?php
namespace Novosga\Model;

/**
 * AtendimentoHistorico
 * historico de atendimento do banco de dados
 * 
 * @Entity
 * @Table(name="historico_atendimentos")
 */
class AtendimentoHistorico extends AbstractAtendimento 
{
    
    /**
     * @OneToMany(targetEntity="wAtendimentoCodificadoHistorico", mappedBy="atendimento")
     * @var wAtendimentoCodificadoHistorico[]
     */
    protected $codificados;
    
    public function __construct() {
        $this->codificados = new ArrayCollection();
    }
    
    public function getCodificados() {
        return $this->codificados;
    }

    public function setCodificados(Collection $codificados) {
        $this->codificados = $codificados;
        return $this;
    }

}
