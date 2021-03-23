<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use DateTime;
use Exception;
use Novosga\Entity\Atendimento;
use Novosga\Entity\ServicoUnidade;
use Novosga\Infrastructure\StorageInterface;
use Novosga\Service\StorageAwareService;
use Twig\Environment;

/**
 * TicketService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class TicketService extends StorageAwareService
{
    /**
     * @var Environment
     */
    private $twig;
    
    public function __construct(StorageInterface $storage, Environment $twig)
    {
        parent::__construct($storage);
        $this->twig = $twig;
    }

    /**
     * Imprime a senha informada pelo atendimento.
     *
     * @param Atendimento $atendimento
     *
     * @throws Exception
     *
     * @return string
     */
    public function printTicket(Atendimento $atendimento)
    {
        // custom view parameters
        $params = [];
        
        $unidade = $atendimento->getUnidade();
        $servico = $atendimento->getServico();
        
        $su = $this->storage
            ->getRepository(ServicoUnidade::class)
            ->get($unidade, $servico);
        
        $viewParams = [
            'atendimento' => $atendimento,
            'servicoUnidade' => $su,
            'now' => new DateTime()
        ];
        
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                $viewParams[$k] = $v;
            }
        }
        
        // custom print template
        $template = null;
        if (empty($template)) {
            $template = 'print.html.twig';
        }
        
        return $this->twig->render($template, $viewParams);
    }
}
