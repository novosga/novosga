<?php
namespace novosga\model;

use \novosga\model\util\Cliente;
use \novosga\model\util\Senha;
use \novosga\business\AtendimentoBusiness;

/**
 * Classe Atendimento
 * contem o Cliente, o Servico e o Status do atendimento
 * 
 * @Entity
 * @Table(name="atendimentos")
 */
class Atendimento extends SequencialModel {

    /**
     * @ManyToOne(targetEntity="ServicoUnidade")
     * @JoinColumns({
     *      @JoinColumn(name="servico_id", referencedColumnName="servico_id"),
     *      @JoinColumn(name="unidade_id", referencedColumnName="unidade_id")
     * })
     */
    protected $servicoUnidade;
    /** 
     * @ManyToOne(targetEntity="Usuario") 
     * @JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    protected $usuario;
    /** 
     * @ManyToOne(targetEntity="Usuario") 
     * @JoinColumn(name="usuario_tri_id", referencedColumnName="id")
     */
    protected $usuarioTriagem;
    /** @Column(type="integer", name="num_guiche", nullable=false) */
    protected $guiche;
    /** @Column(type="datetime", name="dt_cheg", length=50, nullable=false) */
    protected $dataChegada;
    /** @Column(type="datetime", name="dt_cha", length=50, nullable=true) */
    protected $dataChamada;
    /** @Column(type="datetime", name="dt_ini", length=50, nullable=true) */
    protected $dataInicio;
    /** @Column(type="datetime", name="dt_fim", length=50, nullable=true) */
    protected $dataFim;
    /** @Column(type="integer", name="status", length=50, nullable=false) */
    protected $status;
    
    /** @Column(type="string", name="nm_cli", length=100, nullable=true) */
    protected $nomeCliente;
    /** @Column(type="string", name="ident_cli", length=11, nullable=true) */
    protected $documentoCliente;
    /** @Column(type="string", name="sigla_senha", length=1, nullable=false) */
    protected $siglaSenha;
    /** @Column(type="integer", name="num_senha", nullable=false) */
    protected $numeroSenha;
    /** @Column(type="integer", name="num_senha_serv", nullable=false) */
    protected $numeroSenhaServico;
    /** 
     * @ManyToOne(targetEntity="Prioridade") 
     * @JoinColumn(name="prioridade_id", referencedColumnName="id")
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
     * Define o usuario que gerou a senha, tanto na triagem quando ao redirecionar o serviço
     * @param Usuario $usuario
     */
    public function setUsuarioTriagem(Usuario $usuario) {
        $this->usuarioTriagem = $usuario;
    }

    /**
     * Retorna o usuario que gerou a senha
     * @return Usuario usuario
     */
    public function getUsuarioTriagem() {
        return $this->usuarioTriagem;
    }

    /**
     * @return \DateTime 
     */
    public function getDataFim() {
        return $this->dataFim;
    }

    
    public function setDataFim(\DateTime $dataFim) {
        $this->dataFim = $dataFim;
    }

    /**
     * @return \DateTime 
     */
    public function getDataChamada() {
        return $this->dataChamada;
    }

    public function setDataChamada(\DateTime $dataChamada) {
        $this->dataChamada = $dataChamada;
    }

    /**
     * @return \DateTime 
     */
    public function getDataInicio() {
        return $this->dataInicio;
    }

    public function setDataInicio(\DateTime $data) {
        $this->dataInicio = $data;
    }

    /**
     * @return \DateTime 
     */
    public function getDataChegada() {
        return $this->dataChegada;
    }

    public function setDataChegada(\DateTime $data) {
        $this->dataChegada = $data;
    }

    /**
     * Retorna o tempo de espera do cliente até ser atendido.
     * A diferença entre a data de chegada até a data atual.
     * 
     * @return \DateInterval
     */
    public function getTempoEspera() {
        $now = new \DateTime();
        return $now->diff($this->getDataChegada());
    }

    /**
     * Retorna o tempo de permanência do cliente na unidade.
     * A diferença entre a data de chegada até a data de fim de atendimento.
     * 
     * @return \DateInterval
     */
    public function getTempoPermanencia() {
        if ($this->getDataFim()) {
            return $this->getDataFim()->diff($this->getDataChegada());
        }
        return new \DateInterval();
        
    }
    
    /**
     * Retorna o tempo total do atendimento.
     * A diferença entre a data de início e fim do atendimento.
     * 
     * @return \DateInterval
     */
    public function getTempoAtendimento() {
        if ($this->getDataFim()) {
            return $this->getDataFim()->diff($this->getDataInicio());
        }
        return new \DateInterval();
        
    }

    public function getGuiche() {
        return $this->guiche;
    }

    public function setGuiche($guiche) {
        $this->guiche = $guiche;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getNomeStatus() {
        return AtendimentoBusiness::nomeSituacao($this->getStatus());
    }
    
    public function setStatus($status) {
        if (is_int($status) && $status > 0) {
            $this->status = $status;
        } else {
            throw new Exception(_('Erro ao definir status do atendimento. Deve ser maior que zero.'));
        }
    }

    public function getCliente() {
        if (!$this->cliente) {
            $this->cliente = new Cliente();
            $this->cliente->setNome($this->nomeCliente . '');
            $this->cliente->setDocumento($this->documentoCliente . '');
        }
        return $this->cliente;
    }
    
    public function getSenha() {
        if (!$this->senha) {
            $this->senha = new Senha();
            $this->senha->setSigla($this->siglaSenha);
            if (AtendimentoBusiness::isNumeracaoServico()) {
                $this->senha->setNumero((int) $this->numeroSenhaServico);
            } else {
                $this->senha->setNumero((int) $this->numeroSenha);
            }
            $this->senha->setPrioridade($this->prioridadeSenha);
        }
        return $this->senha;
    }
    
    public function getSiglaSenha() {
        return $this->siglaSenha;
    }

    public function setSiglaSenha($siglaSenha) {
        $this->siglaSenha = $siglaSenha;
    }
    
    /**
     * Número da senha incremental do dia
     * @param type $numeroSenha
     */
    public function setNumeroSenha($numeroSenha) {
        $this->numeroSenha = $numeroSenha;
    }
    
    public function getNumeroSenha() {
        return $this->numeroSenha;
    }
        
    /**
     * Número da senha incremental pelo serviço do atendimento
     * @param type $numeroSenhaServico
     */
    public function setNumeroSenhaServico($numeroSenhaServico) {
        $this->numeroSenhaServico = $numeroSenhaServico;
    }

    public function getNumeroSenhaServico() {
        return $this->numeroSenhaServico;
    }
    
    public function setPrioridadeSenha($prioridadeSenha) {
        $this->prioridadeSenha = $prioridadeSenha;
    }

    
    public function toString() {
        return $this->getSenha()->toString();
    }
    
    public function toArray($minimal = false) {
        $arr = array(
            'id' => $this->getId(),
            'senha' => $this->getSenha()->toString(),
            'servico' => $this->getServicoUnidade()->getNome(),
            'prioridade' => $this->getSenha()->isPrioridade(),
            'nomePrioridade' => $this->getSenha()->getPrioridade()->getNome(),
            'chegada' => $this->getDataChegada()->format('Y-m-d H:i:s'),
            'espera' => $this->getTempoEspera()->format('%H:%I:%S')
        );
        if (!$minimal) {
            $arr['numero'] = $this->getSenha()->toString();
            if ($this->getUsuario()) {
                $arr['usuario'] = $this->getUsuario()->getLogin();
            }
            if ($this->getUsuarioTriagem()) {
                $arr['triagem'] = $this->getUsuarioTriagem()->getLogin();
            }
            if ($this->getDataInicio()) {
                $arr['inicio'] = $this->getDataInicio()->format('Y-m-d H:i:s');
            }
            if ($this->getDataFim()) {
                $arr['fim'] = $this->getDataFim()->format('Y-m-d H:i:s');
            }
            $arr['status'] = $this->getStatus();
            $arr['nomeStatus'] = $this->getNomeStatus();
            $arr['cliente'] = array(
                'nome' => $this->getCliente()->getNome(),
                'documento' => $this->getCliente()->getDocumento()
            );
        }
        return $arr;
    }

}
