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

use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\EnderecoInterface;

/**
 * Endereco
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Embeddable]
class Endereco implements EnderecoInterface
{
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $pais = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $cidade = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $estado = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $cep = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $logradouro = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $complemento = null;

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getComplemento(): ?string
    {
        return $this->complemento;
    }

    public function setComplemento(?string $complemento): static
    {
        $this->complemento = $complemento;

        return $this;
    }

    public function getLogradouro(): ?string
    {
        return $this->logradouro;
    }

    public function setLogradouro(?string $logradouro): static
    {
        $this->logradouro = $logradouro;

        return $this;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function setCep(?string $cep): static
    {
        $this->cep = $cep;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    public function setCidade(?string $cidade): static
    {
        $this->cidade = $cidade;

        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(?string $pais): static
    {
        $this->pais = $pais;

        return $this;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'pais' => $this->getPais(),
            'estado' => $this->getEstado(),
            'cidade' => $this->getCidade(),
            'cep' => $this->getCep(),
            'logradouro' => $this->getLogradouro(),
            'numero' => $this->getNumero(),
            'complemento' => $this->getComplemento(),
        ];
    }
}
