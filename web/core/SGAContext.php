<?php
namespace core;

use \core\SGA;
use \core\http\Session;
use \core\http\Cookie;
use \core\http\Request;
use \core\http\Response;
use \core\model\Usuario;
use \core\model\Modulo;
use \core\model\Unidade;
use \core\util\Arrays;

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
    private $module;
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
    public function getSession() {
        return $this->session;
    }
    
    /**
     * @return Cookie
     */
    public function getCookie() {
        return $this->cookie;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }
    
    /**
     * @return Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @return Usuario
     */
    public function getUser() {
        if ($this->user == null) {
            $this->user = $this->getSession()->getGlobal(SGA::K_CURRENT_USER);
        }
        return $this->user;
    }

    public function setUser(Usuario $user = null) {
        $this->user = $user;
        $this->getSession()->setGlobal(SGA::K_CURRENT_USER, $user);
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
    public function getModule() {
        if ($this->module == null && defined('MODULE')) {
            $query = \core\db\DB::getEntityManager()->createQuery("SELECT m FROM \core\model\Modulo m WHERE m.chave = :chave");
            $query->setParameter('chave', MODULE);
            $this->module = $query->getOneOrNullResult();
        }
        return $this->module;
    }

    public function setModule(Modulo $module = null) {
        $this->module = $module;
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
