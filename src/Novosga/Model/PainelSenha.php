<?php

namespace Novosga\Model;

/**
  * @Entity
  * @Table(name="painel_senha")
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
 class PainelSenha extends SequencialModel
 {
     /**
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="servico_id", referencedColumnName="id", nullable=false)
     */
    protected $servico;

    /**
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     */
    protected $unidade;

    /**
     * @Column(type="integer", name="num_senha", nullable=false)
     */
    protected $numeroSenha;

    /**
     * @Column(type="string", name="sig_senha", length=1, nullable=false)
     */
    protected $siglaSenha;

    /**
     * @Column(type="string", name="msg_senha", length=20, nullable=false)
     */
    protected $mensagem;

    /**
     * @Column(type="string", name="local", length=15, nullable=false)
     */
    protected $local;

    /**
     * @Column(type="smallint", name="num_local", nullable=false)
     */
    protected $numeroLocal;

    /**
     * @Column(type="smallint", name="peso", nullable=false)
     */
    protected $peso;

    /**
     * @Column(type="string", name="prioridade", length=100, nullable=true)
     */
    protected $prioridade;

    /**
     * @Column(type="string", name="nome_cliente", length=100, nullable=true)
     */
    protected $nomeCliente;

    /**
     * @Column(type="string", name="documento_cliente", length=30, nullable=true)
     */
    protected $documentoCliente;

     public function getServico()
     {
         return $this->servico;
     }

     public function setServico($servico)
     {
         $this->servico = $servico;
     }

     public function getUnidade()
     {
         return $this->unidade;
     }

     public function setUnidade($unidade)
     {
         $this->unidade = $unidade;
     }

     public function getNumeroSenha()
     {
         return $this->numeroSenha;
     }

     public function setNumeroSenha($numeroSenha)
     {
         $this->numeroSenha = $numeroSenha;
     }

     public function getSiglaSenha()
     {
         return $this->siglaSenha;
     }

     public function setSiglaSenha($siglaSenha)
     {
         $this->siglaSenha = $siglaSenha;
     }

     public function getMensagem()
     {
         return $this->mensagem;
     }

     public function setMensagem($mensagem)
     {
         $this->mensagem = $mensagem;
     }

     public function getLocal()
     {
         return $this->local;
     }

     public function setLocal($local)
     {
         $this->local = $local;
     }

     public function getNumeroLocal()
     {
         return $this->numeroLocal;
     }

     public function setNumeroLocal($numeroLocal)
     {
         $this->numeroLocal = $numeroLocal;
     }

     public function getPeso()
     {
         return $this->peso;
     }

     public function setPeso($peso)
     {
         $this->peso = $peso;
     }

     public function getPrioridade()
     {
         return $this->prioridade;
     }

     public function getNomeCliente()
     {
         return $this->nomeCliente;
     }

     public function getDocumentoCliente()
     {
         return $this->documentoCliente;
     }

     public function setPrioridade($prioridade)
     {
         $this->prioridade = $prioridade;
     }

     public function setNomeCliente($nomeCliente)
     {
         $this->nomeCliente = $nomeCliente;
     }

     public function setDocumentoCliente($documentoCliente)
     {
         $this->documentoCliente = $documentoCliente;
     }

     public function jsonSerialize()
     {
         return array(
            'id' => $this->getId(),
            'senha' => $this->getSiglaSenha().str_pad($this->getNumeroSenha(), 3, '0', STR_PAD_LEFT),
            'local' => $this->getLocal(),
            'numeroLocal' => $this->getNumeroLocal(),
            'peso' => $this->getPeso(),
            'prioridade' => $this->getPrioridade(),
            'nomeCliente' => $this->getNomeCliente(),
            'documentoCliente' => $this->getDocumentoCliente(),
        );
     }
 }
