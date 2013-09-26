<?php
namespace novosga\controller;

use \novosga\SGA;
use \novosga\SGAContext;
use \novosga\util\Arrays;
use \novosga\business\AcessoBusiness;
use \novosga\controller\InternalController;

/**
 * LoginController
 *
 * @author rogeriolino
 */
class LoginController extends InternalController {
    
    public function index(SGAContext $context) {
        if (AcessoBusiness::isLogged()) {
            if (AcessoBusiness::isValidSession()) {
                if ($context->getModulo()) {
                    $this->app()->gotoModule();
                } else {
                    $this->app()->gotoHome();
                }
            } else {
                $user = $context->getUser();
                if ($user != null) {
                    if (!$user->isAtivo()) {
                        $this->app()->view()->assign('error', _('Sessão expirada. Favor efetuar o login novamente.'));
                    } else {
                        $this->app()->view()->assign('error', _('Sessão Inválida. Possivelmente o seu usuário está sendo utilizado em outra máquina.'));
                    }
                    $context->setUser(null);
                }
            }
        }
    }
    
    public function validate(SGAContext $context) {
        $username = Arrays::value($_POST, 'username');
        $password = Arrays::value($_POST, 'password');
        $error = null;
        if (!empty($username) && !empty($password)) {
            $user = SGA::auth($username, $password);
            if ($user) {
                // atualizando o session id
                $em = \novosga\db\DB::getEntityManager();
                $user->setSessionId(session_id());
                $user->setUltimoAcesso(\novosga\util\DateUtil::nowSQL());
                $em->merge($user);
                $em->flush();
                // caso o usuario so tenha acesso a uma unica unidade, ja define como atual
                $us = new \novosga\model\util\UsuarioSessao($user);
                $unidades = AcessoBusiness::unidades($us);
                if (sizeof($unidades) == 1) {
                    $us->setUnidade($unidades[0]);
                }
                $context->setUser($us);
            } else {
                $error = _('Usuário ou senha inválido');
            }
        }
        if (!$error) {
            $this->app()->gotoHome();
        } else {
            $this->app()->flash('error', $error);
            $this->app()->gotoLogin();
        }
    }
    
}
