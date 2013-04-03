<?php
namespace core\model\util;

use \core\db\DB;
use \core\model\Usuario;
use \core\model\Unidade;

/**
 * Usuario utilizado para salvar na sessao. Assim evitar de salvar
 * as entidades do Doctrine.
 */
class UsuarioSessao {

    private $id;
    private $unidade;
    private $unidadeId;
    private $guiche;
    private $ativo;
    private $lotacao;
    private $servicos;
    private $servicosIndisponiveis;
    private $sessionId;
    private $permissoes;
    private $wrapped;

    public function __construct(Usuario $usuario) {
        $this->id = $usuario->getId();
        $this->sessionId = $usuario->getSessionId();
        $this->ativo = true;
        $this->wrapped = $usuario;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * Retorna o número do guiche para atendimento na unidade atual
     * @return type
     */
    public function getGuiche() {
        return $this->guiche;
    }

    public function setGuiche($guiche) {
        $guiche = (int) $guiche;
        if ($guiche > 0) {
            $this->guiche = $guiche;
        } else {
            throw new Exception(_('Erro ao definir guiche do Usuário. Deve ser um número maior que zero.'));
        }
    }

    public function isAtivo() {
        return $this->ativo == true;
    }

    public function setAtivo($ativo) {
        $this->ativo = ($ativo == true);
    }

    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * Retorna todas as permissoes do usuario
     */
    public function getPermissoes() {
        if (!$this->permissoes) {
            $this->permissoes = array();
            $query = DB::getEntityManager()->createQuery("
                SELECT
                   p
                FROM
                    \core\model\Lotacao l,
                    \core\model\Permissao p
                WHERE
                    l.cargo = p.cargo AND
                    l.usuario = :usuario
            ");
            $query->setParameter('usuario', $this->getId());
            $permissoes = $query->getResult();
            foreach ($permissoes as $permissao) {
                $this->permissoes[] = new PermissaoSessao($this->getId(), $permissao);
            }
        }
        return $this->permissoes;
    }

    /**
     * Verifica se o usuaro tem permissao no modulo informado. Filtrando tambem
     * por cargo, caso seja informado.
     * @param \core\model\Modulo $modulo
     * @param \core\model\util\Cargo $cargo
     * @return boolean
     */
    public function hasPermissao($modulo, $cargo = null) {
        $permissoes = $this->getPermissoes();
        // fazendo dois for para evitar de colocar outro if dentro do loop
        if ($cargo == null) {
            foreach ($permissoes as $permissao) {
                if ($modulo->getId() == $permissao->getModuloId()) {
                    return true;
                }
            }
        } else {
            foreach ($permissoes as $permissao) {
                if ($modulo->getId() == $permissao->getModuloId() && $cargo->getId() == $permissao->getCargoId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retorna a lotacao do usuario na unidade atual
     * @return \core\model\Lotacao
     */
    public function getLotacao() {
        if (!$this->lotacao) {
            // pegando a lotacao do usuario na unidade escolhida
            $query = DB::getEntityManager()->createQuery("SELECT e FROM \core\model\Lotacao e JOIN e.grupo g WHERE e.usuario = :usuario ORDER BY g.left DESC");
            $query->setParameter('usuario', $this->getId());
            $lotacoes = $query->getResult();
            foreach ($lotacoes as $lotacao) {
                // se o usuario esta ligado a alguma unidade
                if ($this->getUnidade()) {
                    // verifica se a lotacao eh do mesmo grupo ou um grupo pai do grupo da unidade
                    if ($lotacao->getGrupo()->getId() == $this->getUnidade()->getGrupo()->getId() || $this->getUnidade()->getGrupo()->isChild($lotacao->getGrupo())) {
                        $this->lotacao = $lotacao;
                        break;
                    }
                } else {
                    $this->lotacao = $lotacao;
                    break;
                }
            }
        }
        return $this->lotacao;
    }

    /**
     * Retorna os servicos do usuario na unidade atual
     * @return Locatacao
     */
    public function getServicos() {
        if (!$this->servicos && $this->getUnidade()) {
            $query = DB::getEntityManager()->createQuery("SELECT e FROM \core\model\ServicoUsuario e WHERE e.usuario = :usuario AND e.unidade = :unidade");
            $query->setParameter('usuario', $this->getId());
            $query->setParameter('unidade', $this->getUnidade()->getId());
            $this->servicos = $query->getResult();
        }
        return $this->servicos;
    }

    /**
     * Retorna os servicos que o usuario nao atende na unidade atual
     * @return Locatacao
     */
    public function getServicosIndisponiveis() {
        if (!$this->servicosIndisponiveis && $this->getUnidade()) {
            $query = DB::getEntityManager()->createQuery("
                SELECT
                    e
                FROM
                    \core\model\ServicoUnidade e
                    JOIN e.servico s
                WHERE
                    e.status = 1 AND
                    e.unidade = :unidade AND
                    s.id NOT IN (
                        SELECT s2.id FROM \core\model\ServicoUsuario a JOIN a.servico s2 WHERE a.usuario = :usuario AND a.unidade = :unidade
                    )
            ");
            $query->setParameter('usuario', $this->getId());
            $query->setParameter('unidade', $this->getUnidade()->getId());
            $this->servicosIndisponiveis = $query->getResult();
        }
        return $this->servicosIndisponiveis;
    }

    /**
     *
     * @return \core\model\Unidade
     */
    public function getUnidade() {
        if (!$this->unidade && $this->unidadeId > 0) {
            $this->unidade = DB::getEntityManager()->find("\core\model\Unidade", $this->unidadeId);
        }
        return $this->unidade;
    }

    public function setUnidade(Unidade $unidade) {
        $this->unidade = $unidade;
        $this->unidadeId = $unidade->getId();
    }

    /**
     *
     * @return \core\model\Usuario
     */
    public function getWrapped() {
        if (!$this->wrapped) {
            $this->wrapped = DB::getEntityManager()->find("\core\model\Usuario", $this->id);
        }
        return $this->wrapped;
    }

    public function __sleep() {
        return array('id', 'unidadeId', 'sessionId', 'ativo', 'guiche', 'permissoes');
    }

    /**
     * Métodos desconhecidos serão chamados no modelo usuário
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments) {
        $method = new \ReflectionMethod($this->getWrapped(), $name);
        return $method->invokeArgs($this->getWrapped(), $arguments);
    }

}
