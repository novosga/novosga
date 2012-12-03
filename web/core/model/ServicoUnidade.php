<?php
namespace core\model;

use \core\model\Model;
use \core\model\util\Fila;
use \core\model\Servico;
use \core\model\Unidade;

/**
 * Servico Unidade
 * 
 * @author rogeriolino
 * 
 * @Entity
 * @Table(name="uni_serv")
 */
class ServicoUnidade extends Model {
    
    /** 
     * @Id
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="id_serv", referencedColumnName="id_serv")
     */
    protected $servico;
    /**
     * @Id 
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="id_uni", referencedColumnName="id_uni")
     */
    protected $unidade;
    /** @Column(type="string", name="nm_serv", length=50, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="sigla_serv", length=1, nullable=false) */
    protected $sigla;
    /** @Column(type="integer", name="stat_serv", nullable=false) */
    protected $status;
    
    // transient 
    
    private $fila;
	
    public function __construct() {
    }

    /**
     * @return Servico
     */
    public function getServico() {
        return $this->servico;
    }

    public function setServico(Servico $servico) {
        $this->servico = $servico;
    }

    /**
     * @return Unidade
     */
    public function getUnidade() {
        return $this->unidade;
    }

    public function setUnidade(Unidade $unidade) {
        $this->unidade = $unidade;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }
    
    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setSigla($sigla) {
        $this->sigla = $sigla;
    }

    public function getSigla() {
        return $this->sigla;
    }
    
    /**
     * Retorna a fila de atendimentos para esse servico. Fazendo o carregamento sobre demanda
     * @return type
     */
    public function getFila() {
        if (!$this->fila) {
            $query = \core\db\DB::getEntityManager()->createQuery("
                SELECT 
                    e 
                FROM 
                    \core\model\Atendimento e 
                    JOIN e.servicoUnidade su 
                    JOIN e.prioridadeSenha p
                WHERE 
                    su.servico = :servico AND 
                    su.unidade = :unidade
                ORDER BY
                    p.peso DESC,
                    e.numeroSenha ASC
            ");
            $query->setParameter('servico', $this->getServico()->getId());
            $query->setParameter('unidade', $this->getUnidade()->getId());
            $this->fila = new Fila($query->getResult());
        }
        return $this->fila;
    }
    
    public function toString() {
        return $this->sigla . ' - ' . $this->getServico()->toString();
    }
	
}
