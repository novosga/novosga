<?php
namespace login;

use \core\SGA;
use \core\SGAContext;
use \core\db\DB;
use \core\controller\InternalController;

/**
 * LoginController
 *
 * @author rogeriolino
 */
class LoginController extends InternalController {
    
    
    protected function createView() {
        require_once(__DIR__ . '/LoginView.php');
        return new LoginView(_('Login'));
    }
    
    public function index(SGAContext $context) {
        if (SGA::isLogged()) {
            if (SGA::isValidSession()) {
                if (!$context->getModule()) {
                    SGA::redirect('/' . SGA::K_HOME);
                } else {
                    SGA::redirect(array(SGA::K_MODULE => $context->getModule()->getChave()));
                }
            } else {
                $user = $context->getUser();
                if ($user->isAtivo()) {
                    $this->view()->assign('error', _('Sessão Inválida. Possivelmente o seu usuário está sendo utilizado em outra máquina.'));
                    $user->setAtivo(0);
                }
            }
        }
    }
    
    public function validate(SGAContext $context) {
        $context->getSession()->set(SGA::K_LOGIN_ERROR, null);
        if (!empty($_POST['user']) && !empty($_POST['pass'])) {
            $username = $_POST['user'];
            $password = $_POST['pass'];
            $user = SGA::auth($username, $password);
            if ($user) {
                $context->setUser(new \core\model\util\UsuarioSessao($user));
                SGA::redirect('/' . SGA::K_HOME);
            } else {
                $context->getSession()->set(SGA::K_LOGIN_ERROR, _('Usuário Inválido. Por favor, tente novamente.'));
            }
        }
        SGA::redirect('/' . SGA::K_LOGIN);
    }
    
}
