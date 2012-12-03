<?php
namespace core\view;

use \core\view\SGAView;
use \core\SGAContext;

/**
 * Error View
 * 
 * @author rogeriolino
 */
class ErrorView extends SGAView {
    
    public function __construct() {
        parent::__construct(_('Erro'));
    }

    public function content(SGAContext $context) {
        $html = '<div id="error-page"><h1>Oops!</h1>';
        $exception = $context->getParameter('exception');
        if ($exception) {
            $html .= $this->exception($exception);
        } else {
            $error = $context->getParameter('error');
            if ($error) {
                $html .= $this->error($error);
            } else {
                $html .= $this->unknown();
            }
        } 
        $html .= '</div>';
        return $html;
    }
    
    private function exception(\Exception $exception) {
        $html = '';
        $html .= "<h2>Uncaught exception</h2><pre>{$exception->getMessage()}</pre>";
        if ($exception instanceof SGAException && !$exception->showTrace()) {
            $trace = 'Por motivos de segurança o Trace da exception não pode ser exibido';
        } else {
            $trace = $exception->getTraceAsString();
        }
        $html .= "<h2>Trace</h2><pre>{$trace}</pre>";
        return $html;
    }
    
    private function error($error) {
        $html = '';
        $html .= "<h2>Erro</h2><pre>{$error[1]}</pre>";
        $html .= "<h2>Local</h2><pre>{$error[2]}:{$error[3]}</pre>";
        return $html;
    }
    
    private function unknown() {
        return  "<h2>Erro desconhecido</h2><pre>Tente novamente ou contacte o administrador do sistema</pre>";
    }

    protected function basePath(\SGAContext $context) {
    }

}
