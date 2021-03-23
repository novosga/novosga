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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Novosga\Entity\Lotacao;
use Novosga\Entity\ServicoUnidade;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Service\UsuarioService;
use Novosga\Repository\UsuarioRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * UsuarioRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UsuarioRepository extends ServiceEntityRepository implements
    UsuarioRepositoryInterface,
    UserLoaderInterface,
    UserProviderInterface
{
    /**
     * @var UsuarioService
     */
    private $usuarioService;
    
    public function __construct(ManagerRegistry $registry, UsuarioService $usuarioService)
    {
        parent::__construct($registry, Usuario::class);
        $this->usuarioService = $usuarioService;
    }
    
    /**
     * {@inheritdoc}
     */
    public function findByUnidade(Unidade $unidade, Criteria $criteria = null)
    {
        $usuarios = $this
            ->queryBuilderFindByUnidade($unidade, $criteria)
            ->getQuery()
            ->getResult();
        
        return $usuarios;
    }
    
    /**
     * {@inheritdoc}
     */
    public function findByServicoUnidade(ServicoUnidade $servicoUnidade, Criteria $criteria = null)
    {
        $unidade  = $servicoUnidade->getUnidade();
        $servico  = $servicoUnidade->getServico();
        $usuarios = $this
            ->queryBuilderFindByUnidade($unidade, $criteria)
            ->join(\Novosga\Entity\ServicoUsuario::class, 'su', 'WITH', 'su.usuario = e')
            ->andWhere('su.servico = :servico')
            ->setParameter('servico', $servico)
            ->getQuery()
            ->getResult();
        
        return $usuarios;
    }
    
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $usuario = $this->findOneByLogin($username);
        
        if ($usuario) {
            $this->loadLotacao($usuario);
        }
        
        return $usuario;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $em = $this->getEntityManager();

        $usuario = $em->find(Usuario::class, $user->getId());
        $this->loadLotacao($usuario);

        return $usuario;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === Usuario::class;
    }
    
    private function loadLotacao(Usuario $usuario)
    {
        $lotacao = null;
        $unidade = $this->loadUnidade($usuario);

        if ($unidade) {
            if (!$usuario->isAdmin()) {
                $lotacao = $this
                    ->getEntityManager()
                    ->getRepository(Lotacao::class)
                    ->getLotacao($usuario, $unidade);
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
                    $role = self::roleName($modulo);
                    if (!in_array($role, $roles)) {
                        $usuario->addRole($role);
                    }
                }
            }
        }
    }

    public function loadUnidade(Usuario $usuario)
    {
        $meta = $this
            ->usuarioService
            ->meta($usuario, UsuarioService::ATTR_SESSION_UNIDADE);
        $unidade = null;

        if ($meta) {
            $unidade = $this
                ->getEntityManager()
                ->find(Unidade::class, $meta->getValue());
        } else {
            $unidades = $this
                ->getEntityManager()
                ->getRepository(Unidade::class)
                ->findByUsuario($usuario);

            if (count($unidades) > 0) {
                $unidade = $unidades[0];
            }
        }

        if (!$unidade) {
//            throw new Exception('Nenhuma unidade definida para o usuário.');
        }

        return $unidade;
    }

    public function updateUnidade(Usuario $usuario, Unidade $unidade)
    {
        $this
            ->usuarioService
            ->meta($usuario, UsuarioService::ATTR_SESSION_UNIDADE, $unidade->getId());

        return $unidade;
    }
    
    public static function roleName(string $module): string
    {
        $role = 'ROLE_' . strtoupper(str_replace('.', '_', $module));
        
        return $role;
    }
    
    private function queryBuilderFindByUnidade(Unidade $unidade, Criteria $criteria = null)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->leftJoin('e.lotacoes', 'l')
            ->where('e.admin = TRUE OR (e.admin = FALSE AND l.unidade = :unidade)')
            ->setParameters([
                'unidade' => $unidade
            ])
            ->orderBy('e.nome');
        
        if ($criteria) {
            $qb->addCriteria($criteria);
        }
        
        return $qb;
    }
}
