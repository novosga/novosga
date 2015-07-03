<?php

namespace Novosga\Service;

use Exception;
use Novosga\Context;
use Novosga\Model\Modulo;
use Novosga\Model\Util\UsuarioSessao;
use Novosga\Security;

/**
 * AcessoService.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AcessoService
{
    private $modulos = array(
        Modulo::MODULO_GLOBAL => array(),
        Modulo::MODULO_UNIDADE => array(),
    );

    private $unidades = array();

    /**
     * Verifica se a senha informada é válida e a retorna encriptada.
     *
     * @param type $senha
     * @param type $confirmacao
     *
     * @return type
     *
     * @throws Exception
     */
    public function verificaSenha($senha, $confirmacao)
    {
        if (strlen($senha) < 6) {
            throw new Exception(_('A senha deve possuir no mínimo 6 caracteres.'));
        }
        if ($senha != $confirmacao) {
            throw new Exception(_('A confirmação de senha não confere com a senha.'));
        }

        return Security::passEncode($senha);
    }

    public function isLogged(Context $context)
    {
        return $context->getUser() != null;
    }

    public function isValidSession(Context $context)
    {
        $user = $context->getUser();
        if (!$user || !$user->isAtivo()) {
            return false;
        }
        // verificando session id
        $em = $context->database()->createEntityManager();
        $query = $em->createQuery("SELECT u.sessionId FROM Novosga\Model\Usuario u WHERE u.id = :id");
        $query->setParameter('id', $user->getId());
        $rs = $query->getSingleResult();

        return $user->getSessionId() == $rs['sessionId'];
    }

    public function checkAccess(Context $context, $key, $value)
    {
        if (!$this->isValidSession($context)) {
            if ($context->request()->isAjax()) {
                $response = new \Novosga\Http\JsonResponse();
                $response->success = false;
                // verifica se a sessão está inativa ou inválida
                if (!$context->getUser() || !$context->getUser()->isAtivo()) {
                    $response->inactive = true;
                } else {
                    $response->invalid = true;
                }

                return $response;
            } else {
                $context->app()->gotoLogin();
            }
        }
        $modulo = $context->getModulo();
        if (!$modulo) { // invalid or inactive module
            return false;
        }

        return $this->hasAccess($context->getUser(), $modulo);
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
     * @param Novosga\Model\Util\UsuarioSessao $usuario
     * @param Novosga\Model\Modulo             $modulo
     */
    public function hasAccess(UsuarioSessao $usuario, Modulo $modulo)
    {
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

    public function modulos(Context $context, UsuarioSessao $usuario, $tipo)
    {
        if (!empty($this->modulos[$tipo])) {
            return $this->modulos[$tipo];
        }
        $em = $context->database()->createEntityManager();
        $query = $em->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\Modulo e
            WHERE
                e.status = 1 AND
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
        $this->modulos[$tipo] = $modulos;

        return $modulos;
    }

    public function unidades(Context $context, UsuarioSessao $usuario)
    {
        if (!empty($this->unidades)) {
            return $this->unidades;
        }
        $em = $context->database()->createEntityManager();
        $query = $em->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\Unidade e
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
                $this->unidades = array_merge($this->unidades, $query->getResult());
            }
        }

        return $this->unidades;
    }
}
