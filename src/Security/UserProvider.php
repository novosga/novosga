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

namespace App\Security;

use App\Entity\Lotacao;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use App\Entity\Usuario;
use App\Repository\LotacaoRepository;
use App\Repository\UnidadeRepository;
use App\Repository\UsuarioRepository;
use App\Service\UsuarioService;
use Exception;
use Novosga\Entity\LotacaoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserProvider
 *
 * @extends EntityUserProvider<Usuario>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UserProvider extends EntityUserProvider
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UsuarioService $usuarioService,
        private readonly UsuarioRepository $usuarioRepository,
        private readonly UnidadeRepository $unidadeRepository,
        private readonly LotacaoRepository $lotacaoRepository,
    ) {
        parent::__construct($registry, Usuario::class);
    }

    public function supportsClass(string $class): bool
    {
        return $class === Usuario::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $usuario = $this->usuarioRepository->findOneByLogin($identifier);

        if (null === $usuario) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUserIdentifier($identifier);

            throw $e;
        }

        $this->loadLotacao($usuario);

        return $usuario;
    }

    /** @param Usuario $user  */
    public function refreshUser(UserInterface $user): UserInterface
    {
        $usuario = $this->usuarioRepository->find($user->getId());
        if (null === $usuario) {
            throw new UserNotFoundException();
        }

        $this->loadLotacao($usuario);

        return $usuario;
    }

    public function loadLotacao(Usuario $usuario): void
    {
        $lotacao = null;
        $unidade = $this->loadUnidade($usuario);

        if ($unidade) {
            if (!$usuario->isAdmin()) {
                $lotacao = $this->lotacaoRepository->getLotacao($usuario, $unidade);
            } else {
                $lotacao = new Lotacao();
                $lotacao->setUnidade($unidade);
            }

            if (!$lotacao) {
                throw new Exception('Não existe lotação para o usuário atual na unidade informada.');
            }
        }

        $this->loadRoles($usuario, $lotacao);
        $usuario->setLotacao($lotacao);
    }

    public function loadRoles(UsuarioInterface $usuario, ?LotacaoInterface $lotacao = null): void
    {
        $usuario->addRole('ROLE_USER');

        if ($usuario->isAdmin()) {
            $usuario->addRole('ROLE_ADMIN');
        } else {
            $roles = $usuario->getRoles();

            if ($lotacao !== null) {
                $permissoes = $lotacao->getPerfil()->getModulos();

                foreach ($permissoes as $modulo) {
                    $role = self::roleName($modulo);
                    if (!in_array($role, $roles)) {
                        $usuario->addRole($role);
                    }
                }
            }
        }
    }

    public function loadUnidade(Usuario $usuario): ?UnidadeInterface
    {
        $unidade = null;
        $meta = $this
            ->usuarioService
            ->meta($usuario, UsuarioService::ATTR_SESSION_UNIDADE);

        if ($meta) {
            $unidade = $this->unidadeRepository->find($meta->getValue());
        } else {
            $unidades = $this->unidadeRepository->findByUsuario($usuario);
            if (count($unidades) > 0) {
                $unidade = $unidades[0];
            }
        }

        return $unidade;
    }

    public static function roleName(string $module): string
    {
        $role = 'ROLE_' . strtoupper(str_replace('.', '_', $module));

        return $role;
    }
}
