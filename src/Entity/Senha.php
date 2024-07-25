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
use Novosga\Entity\SenhaInterface;

/**
 * Classe Senha
 * Responsavel pelas informacoes do Senha.
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
#[ORM\Embeddable]
class Senha implements SenhaInterface
{
    #[ORM\Column(length: 3)]
    private ?string $sigla = null;

    #[ORM\Column]
    private ?int $numero;

    public function getSigla(): ?string
    {
        return $this->sigla;
    }

    public function setSigla(?string $sigla): static
    {
        $this->sigla = $sigla;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Retorna o numero da senha preenchendo com zero (esquerda).
     */
    public function getNumeroZeros(): string
    {
        return str_pad((string) $this->getNumero(), self::LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Retorna a senha formatada para exibicao.
     */
    public function __toString()
    {
        return $this->getSigla() . $this->getNumeroZeros();
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'sigla'  => $this->getSigla(),
            'numero' => $this->getNumero(),
            'format' => $this->__toString(),
        ];
    }
}
