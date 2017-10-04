<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Lotacao;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Repository\UsuarioRepositoryInterface;
use Novosga\Service\UsuarioService;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * UsuarioRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UsuarioRepository extends EntityRepository implements UsuarioRepositoryInterface, UserLoaderInterface, UserProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $usuario = $this->findOneByLogin($username);
        return $usuario;
    }
    
    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $em = $this->getEntityManager();
        
        $usuario = $em->find(Usuario::class, $user->getId());
        $unidade = $this->loadUnidade($usuario);
        $lotacao = null;
        
        if ($unidade) {
            if (!$usuario->isAdmin()) {
                $lotacao = $em->getRepository(Lotacao::class)->getLotacao($usuario, $unidade);
            } else {
                $lotacao = new Lotacao();
                $lotacao->setUnidade($unidade);
            }

            if (!$lotacao) {
                throw new Exception(_('Não existe lotação para o usuário atual na unidade informada.'));
            }
        }
        
        $this->loadRoles($usuario, $lotacao);
        $usuario->setLotacao($lotacao);
        
        return $usuario;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === Usuario::class;
    }
    
    public function loadRoles(Usuario $usuario, Lotacao $lotacao = null)
    {
        $usuario->addRole('ROLE_USER');
        
        if ($usuario->isAdmin()) {
            $usuario->addRole('ROLE_ADMIN');
        } else {
            $roles = $usuario->getRoles();
        
            if ($lotacao) {
                $permissoes = $lotacao->getPerfil()->getModulos();

                foreach ($permissoes as $modulo) {
                    $role = 'ROLE_' . strtoupper(str_replace('.', '_', $modulo));
                    if (!in_array($role, $roles)) {
                        $usuario->addRole($role);
                    }
                }
            }
        }
    }

    public function loadUnidade(Usuario $usuario)
    {
        $em = $this->getEntityManager();
        $service = new \Novosga\Service\UsuarioService($em);
        $meta = $service->meta($usuario, 'session.unidade');
        $unidade = null;
        
        if ($meta) {
            $unidade = $this->getEntityManager()
                            ->find(Unidade::class, $meta->getValue());
        } else {
            $unidades = $em->getRepository(Unidade::class)->findByUsuario($usuario);

            if (count($unidades) > 0) {
                $unidade = $unidades[0];
            }
        }
        
        if (!$unidade) {
//            throw new Exception(_('Nenhuma unidade definida para o usuário.'));
        }
        
        return $unidade;
    }

    public function updateUnidade(Usuario $usuario, Unidade $unidade)
    {
        $em = $this->getEntityManager();
        $service = new UsuarioService($em);
        $service->meta($usuario, 'session.unidade', $unidade->getId());
        
        return $unidade;
    }
}
