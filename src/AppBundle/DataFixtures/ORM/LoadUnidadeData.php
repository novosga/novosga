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

use Novosga\Entity\Unidade;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoadUnidadeData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $unidade = new Unidade();
        $unidade->setCodigo('UN1');
        $unidade->setNome('Unidade Padrão');
        $unidade->setAtivo(true);
        $unidade->getImpressao()
                    ->setCabecalho('HEADER')
                    ->setRodape('FOOTER')
                    ->setExibirData(true)
                    ->setExibirMensagemServico(true)
                    ->setExibirNomeServico(true)
                    ->setExibirNomeUnidade(true)
                    ->setExibirPrioridade(true)
                ;
        
        $manager->persist($unidade);
        $manager->flush();
        
        $this->addReference('unidade', $unidade);
    }
    
    public function getOrder()
    {
        return 1;
    }
}
