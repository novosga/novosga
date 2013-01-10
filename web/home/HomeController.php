<?php
namespace home;

use \core\SGAContext;
use \core\business\AcessoBusiness;
use \core\controller\SGAController;
use \core\db\DB;
use \core\util\Arrays;
use \core\http\AjaxResponse;

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
        $usuario = $context->getUser();
        $unidade = $usuario->getUnidade();
        // modulos globais
        $this->view()->assign('modulosGlobal', AcessoBusiness::modulos($usuario, \core\model\Modulo::MODULO_GLOBAL));
        // modulos unidades
        if ($unidade) {
            $this->view()->assign('modulosUnidade', AcessoBusiness::modulos($usuario, \core\model\Modulo::MODULO_UNIDADE));
        }
        $this->view()->assign('unidade', $unidade);
        $this->view()->assign('usuario', $usuario);
    }
    
    public function unidade(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->getRequest()->getParameter('unidade');
        try {
            if (!$context->getRequest()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $em = DB::getEntityManager();
            $unidade = $em->find("\core\model\Unidade", $id);
            $context->getUser()->setUnidade($unidade);
            // atualizando a sessao
            $context->setUser($context->getUser());
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
