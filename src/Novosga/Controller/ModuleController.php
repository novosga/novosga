<?php

namespace Novosga\Controller;

use Novosga\App;
use Novosga\Model\Modulo;

/**
 * Classe pai dos controladores dos modulos.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class ModuleController extends AppController
{
    protected $title;
    protected $subtitle;

    public function __construct(App $app, Modulo $modulo)
    {
        parent::__construct($app);
        $this->title = _($modulo->getNome());
        $this->subtitle = _($modulo->getDescricao());
    }
}
