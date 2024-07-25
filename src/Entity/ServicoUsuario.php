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

use App\Repository\ServicoUsuarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUsuarioInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;

/**
 * Servico Usuario
 * Configuração do serviço que o usuário atende
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: ServicoUsuarioRepository::class)]
#[ORM\Table(name: 'servicos_usuarios')]
class ServicoUsuario implements ServicoUsuarioInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Servico::class)]
    private ?ServicoInterface $servico = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    private ?UnidadeInterface $unidade = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    private ?UsuarioInterface $usuario = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $peso = null;

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

    public function getUsuario(): ?UsuarioInterface
    {
        return $this->usuario;
    }

    public function setUsuario(?UsuarioInterface $usuario): static
    {
        $this->usuario = $usuario;

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

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'peso'    => $this->getPeso(),
            'servico' => $this->getServico(),
        ];
    }
}
