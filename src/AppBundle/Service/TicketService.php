<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Service;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Novosga\Config\AppConfig;
use Novosga\Entity\Atendimento;

/**
 * TicketService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class TicketService
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    
    /**
     * @var \Twig_Environment
     */
    private $twig;
    
    public function __construct(ObjectManager $objectManager, \Twig_Environment $twig)
    {
        $this->objectManager = $objectManager;
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
        $params = AppConfig::getInstance()->get('ticket.print.params');
        if (is_callable($params)) {
            $params = $params($atendimento);
        }
        
        $unidade = $atendimento->getUnidade();
        $servico = $atendimento->getServico();
        
        $service = new \Novosga\Service\ServicoService($this->objectManager);
        $servicoUnidade = $service->servicoUnidade($unidade, $servico);
        
        $viewParams = [
            'atendimento' => $atendimento,
            'servicoUnidade' => $servicoUnidade,
            'now' => new DateTime()
        ];
        
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                $viewParams[$k] = $v;
            }
        }
        
        // custom print template
        $template = AppConfig::getInstance()->get('ticket.print.template');
        if (empty($template)) {
            $template = 'print.html.twig';
        }
        
        return $this->twig->render($template, $viewParams);
    }
}
