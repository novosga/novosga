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

use App\Repository\LotacaoRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\LotacaoInterface;
use Novosga\Entity\PerfilInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;

/**
 * Definição de onde o usuário está lotado
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: LotacaoRepository::class)]
#[ORM\Table(name: 'lotacoes')]
class Lotacao implements LotacaoInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "lotacoes_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'lotacoes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UsuarioInterface $usuario = null;

    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?UnidadeInterface $unidade = null;

    #[ORM\ManyToOne(targetEntity: Perfil::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PerfilInterface $perfil = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setUsuario(?UsuarioInterface $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getUsuario(): ?UsuarioInterface
    {
        return $this->usuario;
    }

    public function setUnidade(?UnidadeInterface $unidade): static
    {
        $this->unidade = $unidade;

        return $this;
    }

    public function getUnidade(): ?UnidadeInterface
    {
        return $this->unidade;
    }

    public function setPerfil(?PerfilInterface $perfil): static
    {
        $this->perfil = $perfil;

        return $this;
    }

    public function getPerfil(): ?PerfilInterface
    {
        return $this->perfil;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'      => $this->getId(),
            'perfil'  => $this->getPerfil(),
            'unidade' => $this->getUnidade(),
            'usuario' => $this->getUsuario(),
        ];
    }
}
