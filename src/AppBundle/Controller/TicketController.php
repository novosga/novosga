<?php

namespace AppBundle\Controller;

use DateTime;
use Exception;
use Novosga\Config\AppConfig;
use AppBundle\Entity\Atendimento;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * TicketController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class TicketController extends Controller
{
    /**
     * Imprime a senha informado pelo ID do atendimento e o seu hash.
     *
     * @param Request $request
     * @param int     $id
     * @param hash    $hash
     *
     * @throws Exception
     *
     * @return string
     */
    public function printAction(Request $request, $id, $hash)
    {
        $atendimento = $this->getAtendimento($id);
        if ($hash !== $atendimento->hash()) {
            throw new Exception(_('Chave de segurança do atendimento inválida'));
        }

        return $this->printTicket($atendimento);
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return Atendimento
     */
    public function getAtendimento($id)
    {
        $atendimento = $this->em()->find(Atendimento::class, $id);
        if (!$atendimento) {
            throw new Exception(_('Atendimento inválido'));
        }

        return $atendimento;
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
        
        $viewParams = [
            'atendimento' => $atendimento,
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

        return $this->render($template, $viewParams);
    }
}
