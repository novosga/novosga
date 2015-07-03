<?php

namespace Novosga;

use Novosga\Http\Session;
use Novosga\Http\Cookie;
use Novosga\Http\Request;
use Novosga\Http\Response;
use Novosga\Model\Util\UsuarioSessao;
use Novosga\Model\Modulo;
use Novosga\Model\Unidade;
use Novosga\Util\Arrays;
use Novosga\Config\DatabaseConfig;

/**
 * Context.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Context
{
    const SESSION_CURRENT_USER = 'SGA_CURRENT_USER';

    private $app;
    private $session;
    private $response;
    private $cookie;
    private $user;
    private $modulo;
    private $database;
    private $parameters = array();

    public function __construct(App $app, DatabaseConfig $database)
    {
        $this->app = $app;
        $this->session = new Session();
        $this->cookie = new Cookie();
        $this->response = new Response();
        $this->database = $database;
    }

    /**
     * @return App
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * @return Session
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * @return Cookie
     */
    public function cookie()
    {
        return $this->cookie;
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->app()->request();
    }

    /**
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return \Novosga\Config\DatabaseConfig
     */
    public function database()
    {
        return $this->database;
    }

    /**
     * @return UsuarioSessao
     */
    public function getUser()
    {
        if ($this->user == null) {
            $this->user = $this->session()->getGlobal(self::SESSION_CURRENT_USER);
            if ($this->user) {
                $this->user->setEm($this->database()->createEntityManager());
            }
        }

        return $this->user;
    }

    public function setUser(UsuarioSessao $user = null)
    {
        $this->user = $user;
        $this->session()->setGlobal(self::SESSION_CURRENT_USER, $user);
    }

    /**
     * @return Unidade|null
     */
    public function getUnidade()
    {
        if ($this->getUser()) {
            return $this->getUser()->getUnidade();
        }

        return;
    }

    public function setUnidade(Unidade $unidade = null)
    {
        if ($this->getUser()) {
            $this->getUser()->setUnidade($unidade);
            $this->setUser($this->getUser());
        }
    }

    /**
     * @return Modulo
     */
    public function getModulo()
    {
        if ($this->modulo == null && defined('MODULE')) {
            $query = $this->database->createEntityManager()
                    ->createQuery("SELECT m FROM Novosga\Model\Modulo m WHERE m.chave = :chave");
            $query->setParameter('chave', MODULE);
            $this->modulo = $query->getOneOrNullResult();
            if (!$this->modulo) {
                throw new \Exception(sprintf(_('Módulo "%s" não econtrado.'), MODULE));
            }
        }

        return $this->modulo;
    }

    public function setModule(Modulo $modulo = null)
    {
        $this->modulo = $modulo;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters(array $params)
    {
        $this->parameters = $params;
    }

    public function getParameter($key)
    {
        return Arrays::value($this->parameters, $key);
    }

    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }
}
