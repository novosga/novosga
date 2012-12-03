<?php
namespace core\model;

use \core\model\SequencialModel;
use \core\model\util\Cliente;
use \core\model\util\Senha;
use \core\model\ServicoUnidade;
use \core\model\Usuario;
use \core\model\Prioridade;

/**
 * Classe Atendimento
 * contem o Cliente, o Servico e o Status do atendimento
 * 
 * @Entity
 * @Table(name="atendimentos")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_atend",type="integer")
 *      )
 * })
 */
class Atendimento extends SequencialModel {

    // estados do atendimento
    const SENHA_EMITIDA = 1;
    const CHAMADO_PELA_MESA = 2;
    const ATENDIMENTO_INICIADO = 3;
    const ATENDIMENTO_ENCERRADO = 4;
    const NAO_COMPARECEU = 5;
    const SENHA_CANCELADA = 6;
    const ERRO_TRIAGEM = 7;
    const ATENDIMENTO_ENCERRADO_CODIFICADO = 8;
    
    public static $ARRAY_TODOS_STATUS = array(
        Atendimento::SENHA_EMITIDA,
        Atendimento::CHAMADO_PELA_MESA, 
        Atendimento::ATENDIMENTO_INICIADO, 
        Atendimento::ATENDIMENTO_ENCERRADO, 
        Atendimento::NAO_COMPARECEU, 
        Atendimento::ERRO_TRIAGEM, 
        Atendimento::ATENDIMENTO_ENCERRADO_CODIFICADO
    );
    
    /**
     * @ManyToOne(targetEntity="ServicoUnidade")
     * @JoinColumns({
     *      @JoinColumn(name="id_serv", referencedColumnName="id_serv"),
     *      @JoinColumn(name="id_uni", referencedColumnName="id_uni")
     * })
     */
    protected $servicoUnidade;
    /** 
     * @ManyToOne(targetEntity="Usuario") 
     * @JoinColumn(name="id_usu", referencedColumnName="id_usu")
     */
    protected $usuario;
    /** @Column(type="integer", name="num_guiche", nullable=false) */
    protected $guiche;
    /** @Column(type="string", name="dt_cheg", length=50, nullable=false) */
    protected $dataChegada;
    /** @Column(type="string", name="dt_cha", length=50, nullable=true) */
    protected $dataChamada;
    /** @Column(type="string", name="dt_ini", length=50, nullable=true) */
    protected $dataInicio;
    /** @Column(type="string", name="dt_fim", length=50, nullable=true) */
    protected $dataFim;
    /** @Column(type="integer", name="id_stat", length=50, nullable=false) */
    protected $status;
    
    /** @Column(type="string", name="nm_cli", length=100, nullable=true) */
    protected $nomeCliente;
    /** @Column(type="string", name="ident_cli", length=11, nullable=true) */
    protected $documentoCliente;
    /** @Column(type="integer", name="num_senha", nullable=false) */
    protected $numeroSenha;
    /** 
     * @ManyToOne(targetEntity="Prioridade") 
     * @JoinColumn(name="id_pri", referencedColumnName="id_pri")
     */
    protected $prioridadeSenha;
    
    // transient
    protected $cliente;
    protected $senha;

    /**
     * Retorna o servico inicial do atendimento na unidade.
     * @return ServicoUnidade
     */
    public function getServicoUnidade() {
        return $this->servicoUnidade;
    }

    public function setServicoUnidade(ServicoUnidade $servicoUnidade) {
        $this->servicoUnidade = $servicoUnidade;
    }

    /**
     * Define o usuario que fez o atenidmento (quem atendeu)
     * @param Usuario $usuario
     */
    public function setUsuario(Usuario $usuario) {
        $this->usuario = $usuario;
    }


    /**
     * Retorna o usuario que fez o atendimento (quem atendeu)
     * @return Usuario usuario
     */
    public function getUsuario() {
        return $this->usuario;
    }

    /**
     * Retorna a data fim do Atendimento
     * @return string
     */
    public function getDataFim() {
        return $this->dataFim;
    }

    /**
     * Modifica data final
     * @param $data
     */
    public function setDataFim($data) {
        $this->dataFim = $data;
    }

    /**
     * Retorna data de chamada
     * @return string
     */
    public function getDataChamada() {
        return $this->dataChamada;
    }

    /**
     * Modifica data de chamada
     * @param $data
     */
    public function setDataChamada($data) {
        $this->dataChamada = $data;
    }

    /**
     * Retorna data de inicio
     * @return string
     */
    public function getDataInicio() {
        return $this->dataInicio;
    }

    /**
     * Modifica data de inicio
     * @param $data
     */
    public function setDataInicio($data) {
        $this->dataInicio = $data;
    }

    /**
     * Retorna data de chegada
     * @return string
     */
    public function getDataChegada() {
        return $this->dataChegada;
    }

    /**
     * Modifica data de chegada
     * @param $data
     */
    public function setDataChegada($data) {
        $this->dataChegada = $data;
    }

    /**
     * Retorna o numero do guiche
     * @return string
     */
    public function getGuiche() {
        return $this->guiche;
    }

    /**
     * Modifica o numero do guiche
     * @param $guiche
     */
    public function setGuiche($guiche) {
        $this->guiche = $guiche;
    }
    
    /**
     * Retorna o Status do Atendimento
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Define o Status do Atenidmento
     * @param int $status
     */
    public function setStatus($status) {
        if (is_int($status) && $status > 0) {
            $this->status = $status;
        } else {
            throw new Exception(_('Erro ao definir status do atendimento. Deve ser maior que zero.'));
        }
    }

    /**
     * Retorna o Cliente do Atendimento
     * @return Cliente
     */
    public function getCliente() {
        if (!$this->cliente) {
            $this->cliente = new Cliente();
            $this->cliente->setNome($this->nomeCliente . '');
            $this->cliente->setDocumento($this->documentoCliente . '');
        }
        return $this->cliente;
    }

    /**
    * Retorna a senha do Cliente
    * @return Senha
    */
    public function getSenha() {
        if (!$this->senha) {
            $this->senha = new Senha();
            $this->senha->setSigla($this->servicoUnidade->getSigla());
            $this->senha->setNumero((int) $this->numeroSenha);
            $this->senha->setPrioridade($this->prioridadeSenha);
        }
        return $this->senha;
    }

    /**
     * Retorna String com Id, senha e status
     * @return String
     */
    public function toString() {
        return "Atendimento[id:{$this->getId()},senha:{$this->getCliente()->getSenha()},status: {$this->getStatus()}]";
    }

}
