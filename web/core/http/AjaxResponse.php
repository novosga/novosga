<?php
namespace core\http;

/**
 * AjaxResponse
 *
 * @author rogeriolino
 */
class AjaxResponse {
    
    public $success;
    public $message;
    public $data = array();
    public $sessionActive = true;
    
    public function __construct($success = false, $message = '') {
        $this->success = $success;
        $this->message = $message;
    }
    
}
