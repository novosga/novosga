<?php

namespace AppBundle\Entity;

/**
  * Senha enviada ao painel
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
 class PainelSenha extends SequencialModel
 {
     /**
     * @var Servico
     */
    protected $servico;

    /**
     * @var Unidade
     */
    protected $unidade;

    /**
     * @var int
     */
    protected $numeroSenha;

    /**
     * @var string
     */
    protected $siglaSenha;

    /**
     * @var string
     */
    protected $mensagem;

    /**
     * @var string
     */
    protected $local;

    /**
     * @var int
     */
    protected $numeroLocal;

    /**
     * @var int
     */
    protected $peso;

    /**
     * @var string
     */
    protected $prioridade;

    /**
     * @var string
     */
    protected $nomeCliente;

    /**
     * @var string
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
         return [
            'id'               => $this->getId(),
            'senha'            => $this->getSiglaSenha().str_pad($this->getNumeroSenha(), 3, '0', STR_PAD_LEFT),
            'local'            => $this->getLocal(),
            'numeroLocal'      => $this->getNumeroLocal(),
            'peso'             => $this->getPeso(),
            'prioridade'       => $this->getPrioridade(),
            'nomeCliente'      => $this->getNomeCliente(),
            'documentoCliente' => $this->getDocumentoCliente(),
        ];
     }
 }
