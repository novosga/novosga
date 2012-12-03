<?php
namespace home;

use \core\SGAContext;
use \core\controller\SGAController;
use \core\db\DB;
use \core\util\Arrays;

/**
 * HomeController
 * 
 * @author rogeriolino
 *
 */
class HomeController extends SGAController {

    protected function createView() {
        require_once(__DIR__ . '/HomeView.php');
        return new HomeView();
    }
    
    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->view()->assign('unidade', $unidade);
        $em = DB::getEntityManager();
        $query = $em->createQuery("SELECT m FROM \core\model\Modulo m WHERE m.tipo = :tipo");
        $query->setParameter('tipo', \core\model\Modulo::MODULO_GLOBAL);
        $this->view()->assign('modulosGlobal', $query->getResult());
        if ($unidade) {
            $query->setParameter('tipo', \core\model\Modulo::MODULO_UNIDADE);
            $this->view()->assign('modulosUnidade', $query->getResult());
        }
    }
    
    public function unidade(SGAContext $context) {
        if ($context->getRequest()->isPost()) {
            $id = (int) Arrays::value($_POST, 'unidade');
            $unidade = DB::getEntityManager()->find("\core\model\Unidade", $id);
            $context->getUser()->setUnidade($unidade);
            // atualizando a sessao
            $context->setUser($context->getUser());
            echo json_encode(array('success' => true));
        }
        exit();
    }
    
}
