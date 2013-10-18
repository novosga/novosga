<?php
namespace novosga;

use \Exception;

/**
 * 
 * @author rogeriolino
 */
class SGAException extends Exception {
    
    private $showTrace = true;
    
    public function __construct($message, $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    
    public function showTrace() {
        return $this->showTrace;
    }

    public function setShowTrace($showTrace) {
        $this->showTrace = $showTrace;
    }


}