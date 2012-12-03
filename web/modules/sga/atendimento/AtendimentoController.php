<?php
namespace modules\sga\atendimento;

use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use \core\controller\ModuleController;

/**
 * AtendimentoController
 *
 * @author rogeriolino
 */
class AtendimentoController extends ModuleController {
    
    public function __construct() {
        $this->title = _('Atendimento');
        $this->subtitle = _('Efetue o atendimento às senhas distribuídas dos serviços que você atende');
    }

    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->view()->assign('unidade', $unidade);
        if ($unidade) {
            // servicos
//            $query = $this->em()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade ORDER BY e.nome");
//            $query->setParameter('unidade', $unidade->getId());
//            $this->view()->assign('servicos', $query->getResult());
        }
    }
    
    public function set_guiche(SGAContext $context) {
        $numero = (int) Arrays::value($_POST, 'guiche');
        if ($numero) {
            $context->getCookie()->set('guiche', $numero);
            $context->getSession()->set('guiche', $numero);
        }
        SGA::redirect('index');
    }
    
}
