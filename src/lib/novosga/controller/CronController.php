<?php
namespace novosga\controller;

use \novosga\SGAContext;
use \novosga\http\AjaxResponse;
use \novosga\business\AtendimentoBusiness;
use \novosga\model\util\UsuarioSessao;
use \novosga\db\DB;

/**
 * CronController
 * 
 * @author rogeriolino
 *
 */
class CronController extends SGAController {

    public function index(SGAContext $context) {
        exit();
    }
        
    public function reset(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            // verificando usuario e token de seguranca
            $em = DB::getEntityManager();
            $login = $this->app()->request()->get('login');
            $query = $em->createQuery("SELECT e FROM novosga\model\Usuario e WHERE e.login = :login");
            $query->setParameter('login', $login);
            $query->setMaxResults(1);
            $usuario = $query->getOneOrNullResult();
            if (!$usuario) {
                throw new \Exception(_('Usuário inválido'));
            }
            $token = $this->app()->request()->get('token');
            if ($token != self::token($usuario)) {
                throw new \Exception(_('Token de segurança inválido'));
            }
            // acumulando atendimentos
            AtendimentoBusiness::acumularAtendimentos();
            $response->success = true;
            $response->data['usuario'] = $usuario->getLogin();
            $response->data['timestamp'] = \novosga\util\DateUtil::milis();
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function token($usuario) {
        return \novosga\Security::passEncode($usuario->getId() . ':' . $usuario->getLogin());
    }
    
    public function cronUrl($page, UsuarioSessao $usuario) {
        $url = $this->app()->request()->getUrl();
        $uri = $this->app()->request()->getRootUri();
        $token = $this->token($usuario);
        return "{$url}{$uri}/cron/$page?login={$usuario->getLogin()}&token=$token";
    }
    
}
