<?php
namespace Novosga\Http;

/**
 * Response Wrapper
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Response extends \Slim\Http\Response {
    
    private $renderView = true;
    
    public function setRenderView($bool) {
        return $this->renderView = ($bool == true);
    }
    
    public function renderView() {
        return $this->renderView == true;
    }

}
