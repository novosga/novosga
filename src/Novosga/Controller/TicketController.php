<?php
namespace Novosga\Controller;

use Exception;
use DateTime;
use Novosga\Config\AppConfig;
use Novosga\Context;
use Novosga\Controller\AppController;

/**
 * TicketController
 * 
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 */
class TicketController extends AppController 
{
    
    /**
     * Imprime a senha informado pelo ID do atendimento e o seu hash
     * 
     * @param Context $context
     * @param integer $id
     * @param hash $hash
     * @return string
     * @throws Exception
     */
    public function printAction(Context $context, $id, $hash)
    {
        $atendimento = $this->getAtendimento($id);
        if ($hash !== $atendimento->hash()) {
            throw new Exception(_('Chave de segurança do atendimento inválida'));
        }
        return $this->printTicket($atendimento);
    }
    
    /**
     * 
     * @param integer $id
     * @return \Novosga\Model\Atendimento
     * @throws Exception
     */
    public function getAtendimento($id)
    {
        $atendimento = $this->em()->find("Novosga\Model\Atendimento", $id);
        if (!$atendimento) {
            throw new Exception(_('Atendimento inválido'));
        }
        return $atendimento;
    }
    
    /**
     * Imprime a senha informado pelo ID do atendimento
     * 
     * @param Context $context
     * @param integer $id
     * @return string
     * @throws Exception
     */
    public function printTicket(\Novosga\Model\Atendimento $atendimento)
    {
        // custom view parameters
        $params = AppConfig::getInstance()->get("ticket.print.params");
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                $this->app()->view()->set($k, $v);
            }
        }
        
        $this->app()->view()->set('atendimento', $atendimento);
        $this->app()->view()->set('now', new DateTime());
        
        // custom print template
        $template = AppConfig::getInstance()->get("ticket.print.template");
        if (empty($template)) {
            $template = 'print.html.twig';
        }
        return $template;
    }
    
}
