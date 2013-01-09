<?php
namespace modules\sga\admin;

use \core\SGAContext;
use \core\http\AjaxResponse;
use \core\controller\ModuleController;
use \core\controller\PainelControllerUtil;
use \core\controller\ConfigControllerUtil;

/**
 * AdminView
 * @author rogeriolino
 */
class AdminController extends ModuleController {
    
    public function index(SGAContext $context) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e ORDER BY e.nome");
        $unidades = $query->getResult();
        $paineis = array();
        foreach ($unidades as $unidade) {
            $paineis[$unidade->getId()] = PainelControllerUtil::paineis($unidade);
        }
        $this->view()->assign('unidades', $unidades);
        $this->view()->assign('paineis', $paineis);
    }
    
    public function acumular_atendimentos(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            ConfigControllerUtil::acumularAtendimentos();
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function painel_info(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $unidade = (int) $context->getRequest()->getParameter('unidade');
            $host = (int) $context->getRequest()->getParameter('host');
            $response->data = PainelControllerUtil::painelInfo($unidade, $host);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }

}
