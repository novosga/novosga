<?php

namespace Novosga\Entity;

use Novosga\Entity\Util\Cliente;
use Novosga\Entity\Util\Senha;
use Novosga\Service\AtendimentoService;

/**
 * AbstractAtendimento.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AbstractAtendimento extends SequencialModel
{
    /**
     * @var Unidade
     */
    protected $unidade;

    /**
     * @var Servico
     */
    protected $servico;

    /**
     * @var ServicoUnidade
     */
    private $servicoUnidade;

    /**
     * @var Prioridade
     */
    private $prioridade;

    /**
     * @var Usuario
     */
    protected $usuario;

    /**
     * @var Usuario
     */
    protected $usuarioTriagem;

    /**
     * @var int
     */
    protected $local;

    /**
     * @var \DateTime
     */
    protected $dataChegada;

    /**
     * @var \DateTime
     */
    protected $dataChamada;

    /**
     * @var \DateTime
     */
    private $dataInicio;

    /**
     * @var \DateTime
     */
    private $dataFim;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string
     */
    protected $nomeCliente;

    /**
     * @var string
     */
    protected $documentoCliente;

    /**
     * @var string
     */
    protected $siglaSenha;

    /**
     * @var int
     */
    protected $numeroSenha;

    /**
     * @var Atendimento
     */
    protected $pai;

    // transient
    private $cliente;
    private $senha;

    public function getUnidade()
    {
        return $this->unidade;
    }

    public function setUnidade($unidade)
    {
        $this->unidade = $unidade;

        return $this;
    }

    public function getServico()
    {
        return $this->servico;
    }

    public function setServico($servico)
    {
        $this->servico = $servico;

        return $this;
    }

    public function getServicoUnidade()
    {
        return $this->servicoUnidade;
    }

    public function setServicoUnidade(ServicoUnidade $servicoUnidade)
    {
        $this->servicoUnidade = $servicoUnidade;
        $this->setServico($servicoUnidade->getServico());
        $this->setUnidade($servicoUnidade->getUnidade());

        return $this;
    }

    public function getPrioridade()
    {
        return $this->prioridade;
    }

    public function setPrioridade(Prioridade $prioridade)
    {
        $this->prioridade = $prioridade;

        return $this;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function setUsuarioTriagem(Usuario $usuario)
    {
        $this->usuarioTriagem = $usuario;

        return $this;
    }

    public function getUsuarioTriagem()
    {
        return $this->usuarioTriagem;
    }

    public function getLocal()
    {
        return $this->local;
    }

    public function setLocal($local)
    {
        $this->local = $local;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataChegada()
    {
        return $this->dataChegada;
    }

    public function setDataChegada(\DateTime $dataChegada)
    {
        $this->dataChegada = $dataChegada;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataChamada()
    {
        return $this->dataChamada;
    }

    public function setDataChamada(\DateTime $dataChamada)
    {
        $this->dataChamada = $dataChamada;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataInicio()
    {
        return $this->dataInicio;
    }

    public function setDataInicio(\DateTime $dataInicio)
    {
        $this->dataInicio = $dataInicio;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataFim()
    {
        return $this->dataFim;
    }

    public function setDataFim(\DateTime $dataFim)
    {
        $this->dataFim = $dataFim;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Retorna o nome do status do atendimento.
     *
     * @return type
     */
    public function getNomeStatus()
    {
        return AtendimentoService::nomeSituacao($this->getStatus());
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getNomeCliente()
    {
        return $this->nomeCliente;
    }

    public function setNomeCliente($nomeCliente)
    {
        $this->nomeCliente = $nomeCliente;

        return $this;
    }

    public function getDocumentoCliente()
    {
        return $this->documentoCliente;
    }

    public function setDocumentoCliente($documentoCliente)
    {
        $this->documentoCliente = $documentoCliente;

        return $this;
    }

    public function getSiglaSenha()
    {
        return $this->siglaSenha;
    }

    public function setSiglaSenha($siglaSenha)
    {
        $this->siglaSenha = $siglaSenha;

        return $this;
    }

    public function getNumeroSenha()
    {
        return $this->numeroSenha;
    }

    public function setNumeroSenha($numeroSenha)
    {
        $this->numeroSenha = $numeroSenha;

        return $this;
    }

    public function getPai()
    {
        return $this->pai;
    }

    public function setPai($pai)
    {
        $this->pai = $pai;

        return $this;
    }

    /**
     * Retorna o tempo de espera do cliente até ser atendido.
     * A diferença entre a data de chegada até a data atual.
     *
     * @return \DateInterval
     */
    public function getTempoEspera()
    {
        $now = new \DateTime();

        return $now->diff($this->getDataChegada());
    }

    /**
     * Retorna o tempo de permanência do cliente na unidade.
     * A diferença entre a data de chegada até a data de fim de atendimento.
     *
     * @return \DateInterval
     */
    public function getTempoPermanencia()
    {
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
    public function getTempoAtendimento()
    {
        if ($this->getDataFim()) {
            return $this->getDataFim()->diff($this->getDataInicio());
        }

        return new \DateInterval();
    }

    /**
     * @return Cliente
     */
    public function getCliente()
    {
        if (!$this->cliente) {
            $this->cliente = new Cliente();
            $this->cliente->setNome($this->nomeCliente.'');
            $this->cliente->setDocumento($this->documentoCliente.'');
        }

        return $this->cliente;
    }

    /**
     * @return Senha
     */
    public function getSenha()
    {
        if (!$this->senha) {
            $this->senha = new Senha();
            $this->senha->setSigla($this->siglaSenha);
            $numero = $this->numeroSenha;
            $this->senha->setNumero((int) $numero);
            $this->senha->setPrioridade($this->prioridade);
        }

        return $this->senha;
    }

    public function jsonSerialize($minimal = false)
    {
        $arr = [
            'id'             => $this->getId(),
            'senha'          => $this->getSenha()->toString(),
            'servico'        => $this->getServicoUnidade()->getServico()->getNome(),
            'prioridade'     => $this->getSenha()->isPrioridade(),
            'nomePrioridade' => $this->getSenha()->getPrioridade()->getNome(),
            'chegada'        => $this->getDataChegada()->format('Y-m-d H:i:s'),
            'espera'         => $this->getTempoEspera()->format('%H:%I:%S'),
        ];
        if (!$minimal) {
            $arr['numero'] = $this->getSenha()->getNumero();
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
            $arr['cliente'] = [
                'nome'      => $this->getCliente()->getNome(),
                'documento' => $this->getCliente()->getDocumento(),
            ];
        }

        return $arr;
    }

    public function toString()
    {
        return $this->getSenha()->toString();
    }
}
