<?php
namespace modules\sga\locais;

use novosga\controller\CrudController;
use novosga\model\Local;

/**
 * LocaisController
 *
 * @author rogeriolino
 */
class LocaisController extends CrudController {
    
    protected function createModel() {
        return new Local();
    }
    
    protected function requiredFields() {
        return array('nome');
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\Local e WHERE UPPER(e.nome) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
}
