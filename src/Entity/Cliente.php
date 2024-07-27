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

use App\Repository\ClienteRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\ClienteInterface;
use Novosga\Entity\EnderecoInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Cliente
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: ClienteRepository::class)]
#[ORM\Table(name: 'clientes')]
#[UniqueEntity(fields: ['documento'])]
class Cliente implements ClienteInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "clientes_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $nome = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $documento = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $telefone = null;

    #[ORM\Column(name: 'dt_nascimento', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dataNascimento = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $genero = null;

    #[ORM\Embedded(class: Endereco::class, columnPrefix: 'end_')]
    private EnderecoInterface $endereco;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observacao = null;

    public function __construct()
    {
        $this->endereco = new Endereco();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(?string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    public function setDocumento(?string $documento): static
    {
        $this->documento = $documento;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function setTelefone(?string $telefone): static
    {
        $this->telefone = $telefone;

        return $this;
    }

    public function getDataNascimento(): ?DateTimeInterface
    {
        return $this->dataNascimento;
    }

    public function setDataNascimento(?DateTimeInterface $dataNascimento): static
    {
        $this->dataNascimento = $dataNascimento;

        return $this;
    }

    public function getGenero(): ?string
    {
        return $this->genero;
    }

    public function setGenero(?string $genero): static
    {
        $this->genero = $genero;

        return $this;
    }

    public function getEndereco(): ?EnderecoInterface
    {
        return $this->endereco;
    }

    public function setEndereco(?EnderecoInterface $endereco): static
    {
        $this->endereco = $endereco;

        return $this;
    }

    public function getObservacao(): ?string
    {
        return $this->observacao;
    }

    public function setObservacao(?string $observacao): static
    {
        $this->observacao = $observacao;

        return $this;
    }

    public function __toString()
    {
        return $this->getNome();
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'documento' => $this->getDocumento(),
            'email' => $this->getEmail(),
            'telefone' => $this->getTelefone(),
            'genero' => $this->getGenero(),
            'observacao' => $this->getObservacao(),
            'dataNascimento' => $this->getDataNascimento()?->format('Y-m-d'),
            'endereco' => $this->getEndereco(),
        ];
    }
}
