<?php
namespace modules\sga\admin;

use \core\SGAContext;
use \core\http\AjaxResponse;
use \core\model\Configuracao;
use \core\auth\Authentication;
use \core\controller\ModuleController;
use \core\business\PainelBusiness;
use \core\business\AtendimentoBusiness;
use \cron\CronController;

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
            $paineis[$unidade->getId()] = PainelBusiness::paineis($unidade);
        }
        $auth = Configuracao::get(Authentication::KEY);
        if ($auth) {
            $auth = $auth->getValor();
        } else {
            $auth = array(
                'type' => 'db',
                'db' => array(),
                'ldap' => array(
                    'host' => '',
                    'port' => '',
                    'baseDn' => '',
                    'loginAttribute' => '',
                    'username' => '',
                    'password' => ''
                )
            );
            Configuracao::set(Authentication::KEY, $auth);
        }
        $this->view()->assign('unidades', $unidades);
        $this->view()->assign('paineis', $paineis);
        $this->view()->assign('auth', $auth);
        $this->view()->assign('cronReiniciarSenhas', CronController::cronUrl('reiniciar_senhas', $context->getUser()));
    }
    
    public function auth_save(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $auth = Configuracao::get(Authentication::KEY);
            $value = $auth->getValor();
            $type = $context->getRequest()->getParameter('type');
            $value['type'] = $type;
            if (!isset($value[$type])) {
                $value[$type] = array();
            }
            foreach ($value[$type] as $k => $v) {
                $value[$type][$k] = $context->getRequest()->getParameter($k);
            }
            Configuracao::set(Authentication::KEY, $value);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function acumular_atendimentos(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            AtendimentoBusiness::acumularAtendimentos();
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
            $response->data = PainelBusiness::painelInfo($unidade, $host);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }

}
