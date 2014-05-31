<?php
namespace Novosga\Controller;

use \Novosga\Context;
use \Novosga\Util\Arrays;
use \Novosga\Controller\InternalController;

/**
 * LoginController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoginController extends InternalController {
    
    public function index(Context $context) {
        if ($this->app()->getAcessoBusiness()->isLogged($context)) {
            if ($this->app()->getAcessoBusiness()->isValidSession($context)) {
                if ($context->getModulo()) {
                    $this->app()->gotoModule();
                } else {
                    $this->app()->gotoHome();
                }
            } else {
                $user = $context->getUser();
                if ($user != null) {
                    if (!$user->isAtivo()) {
                        $this->app()->view()->set('error', _('Sessão expirada. Favor efetuar o login novamente.'));
                    } else {
                        $this->app()->view()->set('error', _('Sessão Inválida. Possivelmente o seu usuário está sendo utilizado em outra máquina.'));
                    }
                    $context->setUser(null);
                }
            }
        }
    }
    
    public function validate(Context $context) {
        $username = Arrays::value($_POST, 'username');
        $password = Arrays::value($_POST, 'password');
        $error = null;
        if (!empty($username) && !empty($password)) {
            $user = $this->app()->auth($username, $password);
            if ($user) {
                // atualizando o session id
                $em = $context->database()->createEntityManager();
                $user->setSessionId(session_id());
                $user->setUltimoAcesso(new \DateTime());
                $em->merge($user);
                $em->flush();
                // caso o usuario so tenha acesso a uma unica unidade, ja define como atual
                $us = new \Novosga\Model\Util\UsuarioSessao($user);
                $unidades = $this->app()->getAcessoBusiness()->unidades($context, $us);
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
