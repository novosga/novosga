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
    public $sessionInactive = false;
    
    public function __construct($success = false, $message = '') {
        $this->success = $success;
        $this->message = $message;
    }
    
    /**
     * Retorna o response no formato JSON evitando overhead de campos nulos
     * ou vazios
     * @return string
     */
    public function toJson() {
        $arr = array(
            'success' => ($this->success == true),
            'data' => $this->data
        );
        if (!empty($this->message)) {
            $arr['message'] = $this->message;
        }
        if ($this->sessionInactive) {
            $arr['sessionInactive'] = true;
        }
        return json_encode($arr);
    }
    
}
