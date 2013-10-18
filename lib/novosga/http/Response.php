<?php
namespace novosga\http;

/**
 * SGA Response
 * @author rogeriolino
 */
class Response {
    
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CHARSET = 'utf-8';
    
    private $renderView = true;
    private $contentType = self::CONTENT_TYPE_HTML;
    
    public function setRenderView($bool) {
        return $this->renderView = ($bool == true);
    }
    
    public function renderView() {
        return $this->renderView == true;
    }
    
    public function getContentType() {
        return $this->contentType;
    }

    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }
    
    /**
     * Desabilita o template, e altera o content-type
     */
    public function jsonResponse(AjaxResponse $response) {
        $this->contentType = self::CONTENT_TYPE_JSON;
        $this->updateHeaders();
        echo $response->toJson();
        exit();
    }
    
    public function updateHeaders() {
        header('Content-type: ' . $this->contentType . '; charset=' . self::CHARSET);
    }

}
