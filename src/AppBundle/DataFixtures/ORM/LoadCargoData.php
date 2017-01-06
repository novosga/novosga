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

use Novosga\Entity\Cargo;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadCargoData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $modulos = (new \AppBundle\Service\ModuleService())->getModules();
        
        $cargo = new Cargo();
        $cargo->setNome('Gerente');
        $cargo->setDescricao('Descrição do cargo');
        $cargo->setModulos(array_keys($modulos));
        
        $manager->persist($cargo);
        $manager->flush();
        
        $this->addReference('cargo-gerente', $cargo);
    }
    
    public function getOrder()
    {
        return 1;
    }
}