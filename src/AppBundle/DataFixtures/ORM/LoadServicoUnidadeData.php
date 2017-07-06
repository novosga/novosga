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

use Novosga\Entity\ServicoUnidade;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadServicoUnidadeData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $unidade = $this->getReference('unidade');
        $guiche = $this->getReference('local-guiche');
        
        $i = 1;
        
        do {
            try {
                $servico = $this->getReference('servico-' . $i);
                
                $su = new ServicoUnidade();
                $su->setSigla('S' . $i);
                $su->setIncremento(1);
                $su->setLocal($guiche);
                $su->setMensagem('Mensagem do serviço');
                $su->setNumeroInicial(1);
                $su->setNumeroFinal(999);
                $su->setPeso(1);
                $su->setUnidade($unidade);
                $su->setServico($servico);
                $su->setPrioridade(true);
                $su->setAtivo(true);
                
                $manager->persist($su);
                
                $i++;
            } catch (\Exception $ex) {
                $servico = null;
            }
        } while ($servico);
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 3;
    }
}
