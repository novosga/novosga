<?php
namespace novosga\model;

use \novosga\model\util\Fila;
use \novosga\business\AtendimentoBusiness;

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
     * @JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;
    /**
     * @Id 
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id")
     */
    protected $unidade;
    /**
     * @ManyToOne(targetEntity="Local")
     * @JoinColumn(name="local_id", referencedColumnName="id")
     */
    protected $local;
    /** @Column(type="string", name="nome", length=50, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="sigla", length=1, nullable=false) */
    protected $sigla;
    /** @Column(type="integer", name="status", nullable=false) */
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

    /**
     * @return Local
     */
    public function getLocal() {
        return $this->local;
    }

    public function setLocal(Local $local) {
        $this->local = $local;
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
            $query = \novosga\db\DB::getEntityManager()->createQuery("
                SELECT 
                    e 
                FROM 
                    novosga\model\Atendimento e 
                    JOIN e.servicoUnidade su 
                    JOIN e.prioridadeSenha p
                WHERE 
                    su.servico = :servico AND 
                    su.unidade = :unidade AND
                    e.status = :status
                ORDER BY
                    p.peso DESC,
                    e.numeroSenha ASC
            ");
            $query->setParameter('servico', $this->getServico()->getId());
            $query->setParameter('unidade', $this->getUnidade()->getId());
            $query->setParameter('status', AtendimentoBusiness::SENHA_EMITIDA);
            $this->fila = new Fila($query->getResult());
        }
        return $this->fila;
    }
    
    public function toString() {
        return $this->sigla . ' - ' . $this->getServico()->toString();
    }
	
}
