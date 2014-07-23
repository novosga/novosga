<?php
namespace modules\sga\locais;

use Exception;
use Novosga\Context;
use Novosga\Model\Local;

/**
 * Prioridades DataFixture
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DataFixture {
    
    /**
     * 
     * @param \Novosga\Context $context
     * @throws Exception
     */
    public function install(Context $context) {
        $em = $context->database()->createEntityManager();
        try {
            $em->beginTransaction();
            $em->persist($this->create(_('Guichê')));
            $em->persist($this->create(_('Sala')));
            $em->persist($this->create(_('Mesa')));
            $em->commit();
            $em->flush();
        } catch (Exception $e) {
            try {
                $em->rollback();
            } catch (Exception $ex) {
            }
            throw $e;
        }
    }
    
    /**
     * @param string $name
     * @return \Novosga\Model\Local
     */
    public function create($name) {
        $local = new Local();
        $local->setNome($name);
        return $local;
    }
    
}
