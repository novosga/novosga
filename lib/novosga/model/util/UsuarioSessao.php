<?php
namespace novosga\model\util;

use \novosga\db\DB;
use \novosga\model\Usuario;
use \novosga\model\Unidade;

/**
 * Usuario utilizado para salvar na sessao. Assim evitar de salvar
 * as entidades do Doctrine.
 */
class UsuarioSessao {
    
    // tipos de atendimentos
    const ATEND_TODOS        = 1; // qualquer atendimento
    const ATEND_CONVENCIONAL = 2; // atendimento sem prioridade
    const ATEND_PRIORIDADE   = 3; // atendimento prioritário
    
    private $id;
    private $unidade;
    private $unidadeId;
    private $local;
    private $ativo;
    private $lotacao;
    private $servicos;
    private $servicosIndisponiveis;
    private $sessionId;
    private $permissoes;
    private $tipoAtendimento;
    private $wrapped;
    
    public function __construct(Usuario $usuario) {
        $this->id = $usuario->getId();
        $this->sessionId = $usuario->getSessionId();
        $this->ativo = true;
        $this->tipoAtendimento = self::ATEND_TODOS;
        $this->wrapped = $usuario;
    }
    
    public function getId() {
        return $this->id;
    }
    
    /**
     * Retorna o número do local de atendimento (guiche, mesa, sala, etc) para atendimento na unidade atual
     * @return integer
     */
    public function getLocal() {
        return $this->local;
    }

    public function setLocal($local) {
        $local = (int) $local;
        if ($local > 0) {
            $this->local = $local;
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
                    novosga\model\Lotacao l,
                    novosga\model\Permissao p
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
     * @param novosga\model\Modulo $modulo
     * @param novosga\model\util\Cargo $cargo
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
     * @return novosga\model\Lotacao
     */
    public function getLotacao() {
        if (!$this->lotacao) {
            // pegando a lotacao do usuario na unidade escolhida
            $query = DB::getEntityManager()->createQuery("SELECT e FROM novosga\model\Lotacao e JOIN e.grupo g WHERE e.usuario = :usuario ORDER BY g.left DESC");
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
            $query = DB::getEntityManager()->createQuery("
                SELECT 
                    e 
                FROM 
                    novosga\model\ServicoUsuario e 
                    JOIN 
                        e.servico s 
                WHERE 
                    e.usuario = :usuario AND 
                    e.unidade = :unidade AND 
                    s.status = 1
            ");
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
                    novosga\model\ServicoUnidade e
                    JOIN e.servico s
                WHERE 
                    e.status = 1 AND
                    e.unidade = :unidade AND
                    s.id NOT IN (
                        SELECT s2.id FROM novosga\model\ServicoUsuario a JOIN a.servico s2 WHERE a.usuario = :usuario AND a.unidade = :unidade
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
     * @return novosga\model\Unidade
     */
    public function getUnidade() {
        if (!$this->unidade && $this->unidadeId > 0) {
            $this->unidade = DB::getEntityManager()->find("novosga\model\Unidade", $this->unidadeId);
        }
        return $this->unidade;
    }
    
    public function setUnidade(Unidade $unidade) {
        $this->unidade = $unidade;
        $this->unidadeId = $unidade->getId();
    }
    
    public function getTipoAtendimento() {
        return $this->tipoAtendimento;
    }

    public function setTipoAtendimento($tipoAtendimento) {
        $this->tipoAtendimento = $tipoAtendimento;
    }
    
    public function getLogin() {
        return $this->getWrapped()->getLogin();
    }
    
    public function getNome() {
        return $this->getWrapped()->getNome();
    }
    
    public function getSobrenome() {
        return $this->getWrapped()->getSobrenome();
    }
    
    public function getSenha() {
        return $this->getWrapped()->getSenha();
    }
        
    /**
     * 
     * @return novosga\model\Usuario
     */
    public function getWrapped() {
        if (!$this->wrapped) {
            $this->wrapped = DB::getEntityManager()->find("novosga\model\Usuario", $this->id);
        }
        return $this->wrapped;
    }
    
    public function __sleep() {
        return array('id', 'unidadeId', 'sessionId', 'ativo', 'local', 'tipoAtendimento', 'permissoes');
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
    
    public function __toString() {
        return $this->getNome();
    }

}
