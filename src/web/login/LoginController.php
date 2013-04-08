<?php
namespace login;

use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use \core\business\AcessoBusiness;
use \core\controller\InternalController;

/**
 * LoginController
 *
 * @author rogeriolino
 */
class LoginController extends InternalController {

    const INACTIVE_SESSION = 'Sessão expirada. Favor efetuar o login novamente.';
    const INVALID_SESSION = 'Sessão Inválida. Possivelmente o seu usuário está sendo utilizado em outra máquina.';

    protected function createView() {
        require_once(__DIR__ . '/LoginView.php');
        return new LoginView(_('Login'));
    }

    public function index(SGAContext $context) {
        if (AcessoBusiness::isLogged()) {
            if (AcessoBusiness::isValidSession()) {
                if (!$context->getModulo()) {
                    SGA::redirect('/' . SGA::K_HOME);
                } else {
                    SGA::redirect(array(SGA::K_MODULE => $context->getModulo()->getChave()));
                }
            } else {
                $user = $context->getUser();
                if ($user != null) {
                    if (!$user->isAtivo()) {
                        $this->view()->assign('error', _(self::INACTIVE_SESSION));
                    } else {
                        $this->view()->assign('error', _(self::INVALID_SESSION));
                    }
                    $context->setUser(null);
                }
            }
        }
    }

    public function validate(SGAContext $context) {
        $username = Arrays::value($_POST, 'user');
        $password = Arrays::value($_POST, 'pass');
        $error = null;
        if (!empty($username) && !empty($password)) {
            $user = SGA::auth($username, $password);
            if ($user) {
                // atualizando o session id
                $em = \core\db\DB::getEntityManager();
                $user->setSessionId(session_id());
                $em->merge($user);
                $em->flush();
                $context->setUser(new \core\model\util\UsuarioSessao($user));
            } else {
                $error = _('Usuário Inválido. Por favor, tente novamente.');
            }
        }
        // autenticando via modal (sessão desativada)
        if ($context->getRequest()->isAjax()) {
            $response = new \core\http\AjaxResponse($error == null);
            if (!$response->success) {
                $response->message = $error;
            }
            $context->getResponse()->jsonResponse($response);
        }
        // autenticando via tela de login
        else {
            $context->getSession()->set(SGA::K_LOGIN_ERROR, $error);
            if (!$error) {
                SGA::redirect('/' . SGA::K_HOME);
            } else {
                SGA::redirect('/' . SGA::K_LOGIN);
            }
        }
    }

}
