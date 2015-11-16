<?php

namespace Novosga\Controller;

use Novosga\App;
use Novosga\Context;

/**
 * App module controller.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AppController
{
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @return App
     */
    final public function app()
    {
        return $this->app;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function em()
    {
        return $this->app()->getContext()->database()->createEntityManager();
    }

    public function index(Context $context)
    {
    }
}
