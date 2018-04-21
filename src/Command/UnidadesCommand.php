<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UnidadesCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadesCommand extends Command
{
    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(ObjectManager $om)
    {
        parent::__construct();
        $this->om = $om;
    }

    protected function configure()
    {
        $this->setName('novosga:unidades')
            ->setDescription('Lista as unidades do sistema e seus respectivos ids.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $unidades = $this->om->getRepository(\Novosga\Entity\Unidade::class)
                ->findBy(['ativo' => true], ['id' => 'ASC']);
        $output->writeln('<info>Unidades</info>');
        foreach ($unidades as $unidade) {
            $output->writeln("Id: {$unidade->getId()}, Nome: {$unidade->getNome()}, Descrição: {$unidade->getDescricao()}");
        }
    }
}
