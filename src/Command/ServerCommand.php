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

use Exception;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Ratchet\Server\IoServer;

/**
 * ServerCommand
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServerCommand extends Command
{
    
    private $em;
    
    public function __construct(EntityManager $em = null, $name = null)
    {
        parent::__construct($name = null);
        $this->em = $em;
    }
    
    protected function configure()
    {
        $this->setName('server:painel')
            ->setDescription('Start painel server')
            ->addArgument('port', InputArgument::OPTIONAL, 'Server port', 8080);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new \Novosga\Server\PainelServer($output)
                )
            ),
            (int) $input->getArgument('port')
        );

        $server->run();
    }
}
