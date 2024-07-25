<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\PainelSenhaInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;

/**
  * Senha enviada ao painel
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
#[ORM\Entity]
#[ORM\Table(name: 'painel_senha')]
class PainelSenha implements PainelSenhaInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "painel_senha_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Servico::class)]
    private ?ServicoInterface $servico = null;

    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    private ?UnidadeInterface $unidade = null;

    #[ORM\Column(name: 'num_senha', length: 11)]
    private ?int $numeroSenha = null;

    #[ORM\Column(name: 'sig_senha', length: 3)]
    private ?string $siglaSenha = null;

    #[ORM\Column(name: 'msg_senha', length: 255)]
    private ?string $mensagem = null;

    #[ORM\Column(length: 20)]
    private ?string $local = null;

    #[ORM\Column(name: 'num_local', type: Types::SMALLINT)]
    private ?int $numeroLocal = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $peso = null;

    #[ORM\Column(length: 100)]
    private ?string $prioridade = null;

    #[ORM\Column(length: 100)]
    private ?string $nomeCliente = null;

    #[ORM\Column(length: 30)]
    private ?string $documentoCliente = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getServico(): ?ServicoInterface
    {
        return $this->servico;
    }

    public function setServico(?ServicoInterface $servico): static
    {
        $this->servico = $servico;

        return $this;
    }

    public function getUnidade(): ?UnidadeInterface
    {
        return $this->unidade;
    }

    public function setUnidade(?UnidadeInterface $unidade): static
    {
        $this->unidade = $unidade;

        return $this;
    }

    public function getNumeroSenha(): ?int
    {
        return $this->numeroSenha;
    }

    public function setNumeroSenha(?int $numeroSenha): static
    {
        $this->numeroSenha = $numeroSenha;

        return $this;
    }

    public function getSiglaSenha(): ?string
    {
        return $this->siglaSenha;
    }

    public function setSiglaSenha(?string $siglaSenha): static
    {
        $this->siglaSenha = $siglaSenha;

        return $this;
    }

    public function getMensagem(): ?string
    {
        return $this->mensagem;
    }

    public function setMensagem(?string $mensagem): static
    {
        $this->mensagem = $mensagem;

        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(?string $local): static
    {
        $this->local = $local;

        return $this;
    }

    public function getNumeroLocal(): ?int
    {
        return $this->numeroLocal;
    }

    public function setNumeroLocal(?int $numeroLocal): static
    {
        $this->numeroLocal = $numeroLocal;

        return $this;
    }

    public function getPeso(): ?int
    {
        return $this->peso;
    }

    public function setPeso(?int $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function getPrioridade(): ?string
    {
        return $this->prioridade;
    }

    public function setPrioridade(?string $prioridade): static
    {
        $this->prioridade = $prioridade;

        return $this;
    }

    public function getNomeCliente(): ?string
    {
        return $this->nomeCliente;
    }

    public function setNomeCliente(?string $nomeCliente): static
    {
        $this->nomeCliente = $nomeCliente;

        return $this;
    }

    public function getDocumentoCliente(): ?string
    {
        return $this->documentoCliente;
    }

    public function setDocumentoCliente(?string $documentoCliente): static
    {
        $this->documentoCliente = $documentoCliente;

        return $this;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        $senha = $this->getSiglaSenha() . str_pad((string) $this->getNumeroSenha(), 3, '0', STR_PAD_LEFT);

        return [
           'id'               => $this->getId(),
           'senha'            => $senha,
           'local'            => $this->getLocal(),
           'numeroLocal'      => $this->getNumeroLocal(),
           'peso'             => $this->getPeso(),
           'prioridade'       => $this->getPrioridade(),
           'nomeCliente'      => $this->getNomeCliente(),
           'documentoCliente' => $this->getDocumentoCliente(),
        ];
    }
}
