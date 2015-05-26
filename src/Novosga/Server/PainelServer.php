<?php

namespace Novosga\Server;

use Exception;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PainelServer
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PainelServer implements MessageComponentInterface 
{
    
    protected $output;
    protected $clients;
    
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->clients = new \SplObjectStorage;
    }
    
    
    public function onOpen(ConnectionInterface $conn) 
    {
        $this->clients->attach($conn);
        $this->output->writeln("Painel connected! ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg) 
    {
        $this->output->writeln("Message received from {$from->resourceId}: $msg");
    }

    public function onClose(ConnectionInterface $conn) 
    {
        $this->clients->detach($conn);
        $this->output->writeln("Conection closed! ({$conn->resourceId})");
    }

    public function onError(ConnectionInterface $conn, Exception $e) 
    {
        $this->output->writeln("<error>{$e->getMessage()}</error>");
        $conn->close();
    }
    
}