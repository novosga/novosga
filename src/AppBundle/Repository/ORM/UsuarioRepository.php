<?php

namespace AppBundle\Repository\ORM;

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
    public function loadUserByUsername($username) 
    {
        $usuario = $this->findOneByLogin($username);
        return $usuario;
    }
    
    public function loadRoles(Usuario $usuario, Unidade $unidade = null)
    {
        $usuario->addRole('ROLE_USER');
        
        if ($usuario->isAdmin()) {
            $usuario->addRole('ROLE_ADMIN');
        }
        
        if ($unidade) {
            $em = $this->getEntityManager();
            $lotacao = $em->getRepository(Lotacao::class)->getLotacao($usuario, $unidade);
            if (!$lotacao) {
                throw new Exception(_('Não existe lotação para o usuário atual na unidade informada.'));
            }

            $roles = $usuario->getRoles();
            $permissoes = $lotacao->getCargo()->getModulos();
            
            
            foreach ($permissoes as $modulo) {
                $role = 'ROLE_' . strtoupper(str_replace('.', '_', $modulo));
                if (!in_array($role, $roles)) {
                    $usuario->addRole($role);
                }
            }
            
            $service = new UsuarioService($em);
            $service->meta($em->getReference(Usuario::class, $usuario->getId()), 'session.unidade', $unidade->getId());
        }
    }
    
    public function refreshUser(UserInterface $user) {
        $unidade = null;
        $service = new UsuarioService($this->getEntityManager());
        $meta = $service->meta($user, 'session.unidade');
        if ($meta) {
            $unidade = $this->getEntityManager()->getReference(Unidade::class, $meta->getValue());
        }
        $usuario = $this->getEntityManager()->find(Usuario::class, $user->getId());
        $this->loadRoles($usuario, $unidade);
        return $usuario;
    }

    public function supportsClass($class) {
        
    }

}
