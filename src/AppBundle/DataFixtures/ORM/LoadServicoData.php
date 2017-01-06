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

use Novosga\Entity\Servico;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadServicoData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $servicesCount = 5;
        
        for ($i = 1; $i <= $servicesCount; $i++) {
            $servico = new Servico();
            $servico->setNome('Serviço ' . $i);
            $servico->setDescricao('My service ' . $i);
            $servico->setPeso(1);
            $servico->setStatus(1);
            $manager->persist($servico);
            
            $subCount = rand(1, 5);
            
            for ($j = 1; $j <= $subCount; $j++) {
                $sub = new Servico();
                $sub->setNome('Subserviço ' . $i . '-' . $j);
                $sub->setDescricao('My service ' . $i . '-' . $j);
                $sub->setPeso(1);
                $sub->setStatus(1);
                $sub->setMestre($servico);
                $manager->persist($sub);
            }
            
            $this->addReference('servico-' . $i, $servico);
        }
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 2;
    }
}