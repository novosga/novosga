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

use Novosga\Entity\Perfil;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadPerfilData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $modulos = (new \AppBundle\Service\ModuleService())->getModules();
        
        $perfil = new Perfil();
        $perfil->setNome('Gerente');
        $perfil->setDescricao('Descrição do perfil');
        $perfil->setModulos(array_keys($modulos));
        
        $manager->persist($perfil);
        $manager->flush();
        
        $this->addReference('perfil-gerente', $perfil);
    }
    
    public function getOrder()
    {
        return 1;
    }
}
