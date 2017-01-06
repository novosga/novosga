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

use Novosga\Entity\Local;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadLocalData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $guiche = new Local();
        $guiche->setNome('Guichê');
        $manager->persist($guiche);
        
        $mesa = new Local();
        $mesa->setNome('Mesa');
        $manager->persist($mesa);
        
        $sala = new Local();
        $sala->setNome('Sala');
        $manager->persist($sala);
        
        $manager->flush();
        
        $this->addReference('local-guiche', $guiche);
    }
    
    public function getOrder()
    {
        return 1;
    }
}