<?php
namespace novosga;

use \novosga\SGA;
use \novosga\http\Session;
use \novosga\http\Cookie;
use \novosga\http\Request;
use \novosga\http\Response;
use \novosga\model\util\UsuarioSessao;
use \novosga\model\Modulo;
use \novosga\model\Unidade;
use \novosga\util\Arrays;

/**
 * SGAContext
 *
 * @author rogeriolino
 */
class SGAContext {
    
    private $session;
    private $request;
    private $response;
    private $cookie;
    private $user;
    private $modulo;
    private $parameters = array();
    
    public function __construct() {
        $this->session = new Session();
        $this->cookie = new Cookie();
        $this->request = new Request();
        $this->response = new Response();
    }
    
    /**
     * @return Session
     */
    public function session() {
        return $this->session;
    }
    
    /**
     * @return Cookie
     */
    public function cookie() {
        return $this->cookie;
    }

    /**
     * @return Request
     */
    public function request() {
        return $this->request;
    }
    
    /**
     * @return Response
     */
    public function response() {
        return $this->response;
    }

    /**
     * @return UsuarioSessao
     */
    public function getUser() {
        if ($this->user == null) {
            $this->user = $this->session()->getGlobal(SGA::K_CURRENT_USER);
        }
        return $this->user;
    }

    public function setUser(UsuarioSessao $user = null) {
        $this->user = $user;
        $this->session()->setGlobal(SGA::K_CURRENT_USER, $user);
    }

    /**
     * @return Unidade|null
     */
    public function getUnidade() {
        if ($this->getUser()) {
            return $this->getUser()->getUnidade();
        }
        return null;
    }

    public function setUnidade(Unidade $unidade = null) {
        if ($this->getUser()) {
            $this->getUser()->setUnidade($unidade);
            $this->setUser($this->getUser());
        }
    }

    /**
     * @return Modulo
     */
    public function getModulo() {
        if ($this->modulo == null && defined('MODULE')) {
            $query = \novosga\db\DB::getEntityManager()->createQuery("SELECT m FROM novosga\model\Modulo m WHERE m.chave = :chave");
            $query->setParameter('chave', MODULE);
            $this->modulo = $query->getOneOrNullResult();
            if (!$this->modulo) {
                throw new \Exception(sprintf(_('Módulo "%s" não econtrado.'), MODULE));
            }
        }
        return $this->modulo;
    }

    public function setModule(Modulo $modulo = null) {
        $this->modulo = $modulo;
    }
    
    public function getParameters() {
        return $this->parameters;
    }
    
    public function setParameters(array $params) {
        $this->parameters = $params;
    }
    
    public function getParameter($key) {
        return Arrays::value($this->parameters, $key);
    }
    
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }
    
}
