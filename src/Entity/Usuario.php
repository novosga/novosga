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

use App\EventListener\TimestampableEntityListener;
use App\EventListener\UsuarioListener;
use App\Repository\UsuarioRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Novosga\Entity\LotacaoInterface;
use Novosga\Entity\UsuarioInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Usuario
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
    UsuarioListener::class,
])]
#[ORM\Table(name: 'usuarios')]
class Usuario implements
    UsuarioInterface,
    TimestampableEntityInterface,
    SoftDeletableEntityInterface,
    JsonSerializable,
    EquatableInterface,
    UserInterface,
    PasswordAuthenticatedUserInterface,
    PasswordHasherAwareInterface
{
    use TimestampableEntityTrait;
    use SoftDeletableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "usuarios_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $login = null;

    #[ORM\Column(length: 20)]
    private ?string $nome = null;

    #[ORM\Column(length: 100)]
    private ?string $sobrenome = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 128)]
    private ?string $senha = null;

    #[ORM\Column]
    private bool $ativo = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $ultimoAcesso = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sessionId = null;

    /** @var Collection<int,LotacaoInterface> */
    #[ORM\OneToMany(targetEntity: Lotacao::class, mappedBy: 'usuario')]
    private Collection $lotacoes;

    #[ORM\Column]
    private bool $admin = false;

    #[ORM\Column(length: 10)]
    private ?string $algorithm;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $salt = null;

    // transient
    /** @var string[] */
    private array $roles = [];

    // transient
    private ?LotacaoInterface $lotacao = null;

    public function __construct()
    {
        $this->lotacoes = new ArrayCollection();
        $this->algorithm = 'bcrypt';
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

    public function setLogin(?string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setNome(?string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setSobrenome(?string $sobrenome): static
    {
        $this->sobrenome = $sobrenome;

        return $this;
    }

    public function getSobrenome(): ?string
    {
        return $this->sobrenome;
    }

    /**
     * Retorna o nome completo do usuario (nome + sobrenome).
     *
     * @return string
     */
    public function getNomeCompleto(): string
    {
        return $this->nome . ' ' . $this->sobrenome;
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

    public function getSenha(): ?string
    {
        return $this->senha;
    }

    public function setSenha(?string $senha): static
    {
        $this->senha = $senha;

        return $this;
    }

    public function setAtivo(bool $ativo): static
    {
        $this->ativo = $ativo;

        return $this;
    }

    public function getLotacao(): ?LotacaoInterface
    {
        return $this->lotacao;
    }

    public function setLotacao(?LotacaoInterface $lotacao): static
    {
        $this->lotacao = $lotacao;

        return $this;
    }

    public function getLotacoes(): Collection
    {
        return $this->lotacoes;
    }

    public function setSalt(?string $salt): static
    {
        $this->salt = $salt;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /** @param Collection<int,LotacaoInterface> $lotacoes */
    public function setLotacoes(Collection $lotacoes): static
    {
        $this->lotacoes = $lotacoes;

        return $this;
    }

    public function addLotacoe(Lotacao $lotacao): static
    {
        $lotacao->setUsuario($this);
        $this->getLotacoes()->add($lotacao);

        return $this;
    }

    public function removeLotacoe(Lotacao $lotacao): static
    {
        $this->getLotacoes()->removeElement($lotacao);

        return $this;
    }

    public function isAtivo(): bool
    {
        return (bool) $this->ativo;
    }

    public function getUltimoAcesso(): ?DateTimeInterface
    {
        return $this->ultimoAcesso;
    }

    public function setUltimoAcesso(?DateTimeInterface $ultimoAcesso): static
    {
        $this->ultimoAcesso = $ultimoAcesso;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(?string $algorithm): static
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function isEnabled(): bool
    {
        return !$this->getDeletedAt() && $this->isAtivo();
    }

    public function eraseCredentials(): void
    {
    }

    public function getPassword(): ?string
    {
        return $this->getSenha();
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function addRole(string $role): static
    {
        $this->roles[] = $role;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->getLogin();
    }

    public function getPasswordHasherName(): ?string
    {
        return $this->algorithm;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof Usuario && $user->getId() === $this->getId();
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->getId(),
            'login'     => $this->getLogin(),
            'nome'      => $this->getNome(),
            'sobrenome' => $this->getSobrenome(),
            'ativo'     => $this->isAtivo(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d\TH:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d\TH:i:s') : null,
            'deletedAt' => $this->getDeletedAt() ? $this->getDeletedAt()->format('Y-m-d\TH:i:s') : null,
        ];
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->login,
            $this->nome,
            $this->sessionId,
            $this->senha,
            $this->salt,
            $this->ativo,
        ];
    }

    /** @param array<mixed> $serialized */
    public function __unserialize(array $serialized)
    {
        list (
            $this->id,
            $this->login,
            $this->nome,
            $this->sessionId,
            $this->senha,
            $this->salt,
            $this->ativo,
        ) = $serialized;
    }

    public function __tostring()
    {
        return (string) $this->getLogin();
    }
}
