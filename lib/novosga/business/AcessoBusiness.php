<?php
namespace novosga\business;

use \Exception;
use \novosga\SGA;
use \novosga\SGAContext;
use \novosga\db\DB;
use \novosga\model\Modulo;
use \novosga\model\util\UsuarioSessao;
use \novosga\Security;

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
    
    private static $unidades = array();
        
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
    
    public static function isValidSession(SGAContext $context) {
        $user = $context->getUser();
        if ($user && !$user->isAtivo()) {
            return false;
        }
        // verificando session id
        $em = \novosga\db\DB::getEntityManager();
        $query = $em->createQuery("SELECT u.sessionId FROM novosga\model\Usuario u WHERE u.id = :id");
        $query->setParameter('id', $user->getId());
        $rs = $query->getSingleResult();
        return $user->getSessionId() == $rs['sessionId'];
    }
    
    public static function checkAccess(SGAContext $context, $key, $value) {
        if (!self::isValidSession($context)) {
            if ($context->request()->isAjax()) {
                $response = new \novosga\http\AjaxResponse();
                $response->success = false;
                // verifica se a sessão está inativa ou inválida
                if (!$context->getUser() || !$context->getUser()->isAtivo()) {
                    $response->inactive = true;
                } else {
                    $response->invalid = true;
                }
                $context->response()->jsonResponse($response);
            } else {
                $this->app()->redirect('/login');
            }
        }
        $modulo = $context->getModulo();
        if (!$modulo) { // invalid or inactive module
            return false;
        }
        return self::hasAccess($context->getUser(), $modulo);
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
     * @param novosga\model\util\UsuarioSessao $usuario
     * @param novosga\model\Modulo $modulo
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
                novosga\model\Modulo e
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
    
    public static function unidades(UsuarioSessao $usuario) {
        if (!empty(self::$unidades)) {
            return self::$unidades;
        }
        $em = DB::getEntityManager();
        $query = $em->createQuery("
            SELECT 
                e
            FROM 
                novosga\model\Unidade e
                INNER JOIN e.grupo g
            WHERE 
                e.status = 1 AND
                g.left >= :esquerda AND
                g.right <= :direita
            ORDER BY
                e.nome
        ");
        $lotacoes = $usuario->getWrapped()->getLotacoes();
        if (!empty($lotacoes)) {
            foreach ($lotacoes as $lotacao) {
                $query->setParameter('esquerda', $lotacao->getGrupo()->getLeft());
                $query->setParameter('direita', $lotacao->getGrupo()->getRight());
                self::$unidades = array_merge(self::$unidades, $query->getResult());
            }
        }
        return self::$unidades;
    }
    
}
