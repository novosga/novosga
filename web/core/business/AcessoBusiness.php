<?php
namespace core\business;

use \Exception;
use \core\SGA;
use \core\db\DB;
use \core\model\Modulo;
use \core\model\util\UsuarioSessao;
use \core\Security;

/**
 * AcessoBusiness
 *
 * @author rogeriolino
 */
abstract class AcessoBusiness {
    
    private static $modulos = array(
        Modulo::MODULO_GLOBAL => array(),
        Modulo::MODULO_UNIDADE => array()
    );
    
    public static function isLoginPage($key) {
        return $key == SGA::K_LOGIN;
    }
    
    public static function isHomePage($key) {
        return $key == SGA::K_HOME;
    }
    
    public static function isModulePage($key) {
        return $key == SGA::K_MODULE;
    }
        
    public static function isLogged() {
        return SGA::getContext()->getUser() != null;
    }
    
    public static function isProtectedPage($key) {
        return self::isHomePage($key) || self::isModulePage($key);
    }
    
    /**
     * Verifica se a senha informada é válida e a retorna encriptada.
     * @param type $senha
     * @param type $confirmacao
     * @return type
     * @throws Exception
     */
    public static function verificaSenha($senha, $confirmacao) {
        if (strlen($senha) < 6) {
            throw new Exception(_('A senha deve possuir no mínimo 6 caracteres.'));
        }
        if ($senha != $confirmacao) {
            throw new Exception(_('A confirmação de senha não confere com a senha.'));
        }
        return Security::passEncode($senha);
    }
    
    public static function isValidSession() {
        $user = SGA::getContext()->getUser();
        if (!$user->isAtivo()) {
            return false;
        }
        // verificando session id
        $em = \core\db\DB::getEntityManager();
        $query = $em->createQuery("SELECT u.sessionId FROM \core\model\Usuario u WHERE u.id = :id");
        $query->setParameter('id', $user->getId());
        $rs = $query->getSingleResult();
        return $user->getSessionId() == $rs['sessionId'];
    }
    
    public static function checkAccess($key, $value) {
        if (self::isProtectedPage($key)) {
            $context = SGA::getContext();
            if (!self::isLogged() || !self::isValidSession()) {
                if ($context->getRequest()->isAjax()) {
                    $response = new \core\http\AjaxResponse();
                    $response->success = false;
                    $response->sessionInactive = true;
                    $context->getResponse()->jsonResponse($response);
                } else {
                    SGA::redirect('/' . SGA::K_LOGIN);
                }
            }
            if (self::isHomePage($key)) {
                return true;
            }
            if (self::isModulePage($key)) {
                define('MODULE', $value);
            }
            $modulo = $context->getModulo();
            if (!$modulo) { // invalid or inactive module
                return false;
            }
            if (self::hasAccess($context->getUser(), $modulo)) {
                return true;
            }
            // TODO: adicionar mensagem de acesso negado
            SGA::redirect('/' . SGA::K_HOME);
        }
    }

    /**
     * Verifica se o usuário tem acesso a um determinado módulo.
     * 
     * Quando for um módulo de unidade, basta verificar se existe uma permissão
     * para a lotação do usuário no grupo da unidade.
     * 
     * Já quando o módulo for global, verifica se existe alguma lotação para o 
     * módulo, independente do grupo (unidade).
     * 
     * @param \core\model\util\UsuarioSessao $usuario
     * @param \core\model\Modulo $modulo
     */
    public static function hasAccess(UsuarioSessao $usuario, Modulo $modulo) {
        if ($modulo->isGlobal()) {
            // para modulos globais
            return $usuario->hasPermissao($modulo);
        } else {
            if (!$usuario->getUnidade()) {
                throw new Exception(_('Para acessar esse módulo primeiro é necessário escolher uma unidade.'));
            }
            // verificando se existe permissao para a lotacao atual do usuario
            $lotacao = $usuario->getLotacao();
            return $usuario->hasPermissao($modulo, $lotacao->getCargo());
        }
    }
    
    public static function modulos(UsuarioSessao $usuario, $tipo) {
        if (!empty(self::$modulos[$tipo])) {
            return self::$modulos[$tipo];
        }
        $em = DB::getEntityManager();
        $query = $em->createQuery("
            SELECT 
                e
            FROM 
                \core\model\Modulo e
            WHERE 
                e.tipo = :tipo AND
                e.id IN (:ids)
            ORDER BY
                e.id
        ");
        $ids = array(0);
        $permissoes = $usuario->getPermissoes();
        foreach ($permissoes as $permissao) {
            $ids[] = $permissao->getModuloId();
        }
        $query->setParameter('tipo', $tipo);
        $query->setParameter('ids', $ids);
        $modulos = $query->getResult();
        self::$modulos[$tipo] = $modulos;
        return $modulos;
    }
    
}
