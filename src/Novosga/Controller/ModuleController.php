<?php
namespace Novosga\Controller;

use Novosga\SGA;
use Novosga\Model\Modulo;
use Novosga\Controller\SGAController;

/**
 * Classe pai dos controladores dos modulos
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class ModuleController extends SGAController {
    
    protected $title;
    protected $subtitle;
    
    public function __construct(SGA $app, Modulo $modulo) {
        parent::__construct($app);
        $this->title = _($modulo->getNome());
        $this->subtitle = _($modulo->getDescricao());
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function em() {
        return $this->app()->getContext()->database()->createEntityManager();
    }
    
}
