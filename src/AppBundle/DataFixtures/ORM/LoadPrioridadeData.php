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

use Novosga\Entity\Prioridade;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadPrioridadeData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $normal = new Prioridade();
        $normal->setNome('Normal');
        $normal->setDescricao('Atendimento convencional');
        $normal->setPeso(0);
        $normal->setStatus(1);
        
        $manager->persist($normal);
        
        $prioridade = new Prioridade();
        $prioridade->setNome('Prioridade');
        $prioridade->setDescricao('Atendimento prioritário');
        $prioridade->setPeso(1);
        $prioridade->setStatus(1);
        
        $manager->persist($prioridade);
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 1;
    }
}