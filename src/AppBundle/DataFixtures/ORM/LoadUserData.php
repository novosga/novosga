<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use Novosga\Entity\Usuario;
use Novosga\Entity\Lotacao;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadUserData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $encoder = $this->container->get('security.password_encoder');
        
        $admin = new Usuario();
        $admin->setAdmin(true);
        $admin->setAlgorithm('bcrypt');
        $admin->setNome('Administrator');
        $admin->setSobrenome('Global');
        $admin->setLogin('admin');
        $admin->setStatus(1);
        $admin->setSenha($encoder->encodePassword($admin, '123456'));
        
        $manager->persist($admin);
        
        $user = new Usuario();
        $user->setAdmin(false);
        $user->setAlgorithm('bcrypt');
        $user->setNome('Rogerio');
        $user->setSobrenome('Lino');
        $user->setLogin('rogerio');
        $user->setStatus(1);
        $user->setSenha($encoder->encodePassword($user, '123456'));
        
        $manager->persist($user);
        
        $unidade = $this->getReference('unidade');
        $perfil   = $this->getReference('perfil-gerente');
        
        $lotacao = new Lotacao();
        $lotacao->setPerfil($perfil);
        $lotacao->setUnidade($unidade);
        $lotacao->setUsuario($user);
        
        $manager->persist($lotacao);
        
        $manager->flush();
        
        $this->addReference('user-admin', $admin);
        $this->addReference('user-nonadmin', $user);
    }
    
    public function getOrder()
    {
        return 2;
    }
}
