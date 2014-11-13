<?php
namespace Novosga\Model;


/**
 * Classe Atendimento
 * contem o Cliente, o Servico e o Status do atendimento
 * 
 * @Entity
 * @Table(name="atendimentos")
 */
class Atendimento extends AbstractAtendimento 
{
    
    /**
     * @OneToMany(targetEntity="AtendimentoCodificado", mappedBy="atendimento")
     * @var AtendimentoCodificado[]
     */
    protected $codificados;
    
    public function __construct() {
        $this->codificados = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getCodificados() {
        return $this->codificados;
    }

    public function setCodificados(Collection $codificados) {
        $this->codificados = $codificados;
        return $this;
    }
    
}