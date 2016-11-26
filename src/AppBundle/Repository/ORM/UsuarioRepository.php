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
        $lotacao = $em->getRepository(Lotacao::class)->getLotacao($usuario, $unidade);
        
        if (!$lotacao) {
            throw new Exception(_('Não existe lotação para o usuário atual na unidade informada.'));
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
    
    public function loadRoles(Usuario $usuario, Lotacao $lotacao)
    {
        $usuario->addRole('ROLE_USER');
        
        if ($usuario->isAdmin()) {
            $usuario->addRole('ROLE_ADMIN');
        }
        
        $roles = $usuario->getRoles();
        $permissoes = $lotacao->getCargo()->getModulos();

        foreach ($permissoes as $modulo) {
            $role = 'ROLE_' . strtoupper(str_replace('.', '_', $modulo));
            if (!in_array($role, $roles)) {
                $usuario->addRole($role);
            }
        }
    }

    public function loadUnidade(Usuario $usuario)
    {
        $em = $this->getEntityManager();
        $service = new \Novosga\Service\UsuarioService($em);
        $meta = $service->meta($usuario, 'session.unidade');
        
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
            throw new Exception(_('Nenhuma unidade definida para o usuário.'));
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