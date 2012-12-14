<?php
namespace core\model\util;

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
    private $sessionId;
    private $wrapped;
    
    public function __construct(Usuario $usuario) {
        $this->id = $usuario->getId();
        $this->sessionId = $usuario->getSessionId();
        $this->ativo = $usuario->isAtivo();
        $this->wrapped = $usuario;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getGuiche() {
        return $this->guiche;
    }

    public function setGuiche($guiche) {
        $this->guiche = $guiche;
    }

    public function isAtivo() {
        return $this->ativo == true;
    }
    
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * Retorna a lotacao do usuario na unidade atual
     * @return Locatacao
     */
    public function getLotacao() {
        if (!$this->lotacao) {
            // pegando a lotacao do usuario na unidade escolhida
            $query = \core\db\DB::getEntityManager()->createQuery("SELECT e FROM \core\model\Lotacao e WHERE e.usuario = :usuario");
            $query->setParameter('usuario', $this->getId());
            $lotacoes = $query->getResult();
            foreach ($lotacoes as $lotacao) {
                // verifica se a lotacao eh do mesmo grupo ou um grupo pai do grupo da unidade
                if ($lotacao->getGrupo()->getId() == $this->getUnidade()->getGrupo()->getId() || $this->getUnidade()->getGrupo()->isChild($lotacao->getGrupo())) {
                    $this->lotacao = $lotacao;
                    break;
                }
            }
        }
        return $this->lotacao;
    }
    
    /**
     * 
     * @return \core\model\Unidade
     */
    public function getUnidade() {
        if (!$this->unidade && $this->unidadeId > 0) {
            $this->unidade = \core\db\DB::getEntityManager()->find("\core\model\Unidade", $this->unidadeId);
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
            $this->wrapped = \core\db\DB::getEntityManager()->find("\core\model\Usuario", $this->id);
        }
        return $this->wrapped;
    }
    
    public function __sleep() {
        return array('id', 'unidadeId', 'sessionId', 'ativo', 'guiche');
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
