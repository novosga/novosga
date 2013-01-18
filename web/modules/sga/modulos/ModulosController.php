<?php
namespace modules\sga\modulos;

use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use \core\model\Modulo;
use \core\view\CrudView;
use \core\http\AjaxResponse;
use \core\controller\ModuleController;

/**
 * ModulosController
 *
 * @author rogeriolino
 */
class ModulosController extends ModuleController {
    
    /**
     * @return CrudView
     */
    protected function createView() {
        return new CrudView($this->title, $this->subtitle);
    }
    
    /**
     * Monta a lista das entidades, podendo filtra-las.
     * @param \core\SGAContext $context
     */
    public function index(SGAContext $context) {
        $context = SGA::getContext();
        if (isset($_POST['s'])) {
            $arg = "%" . strtoupper($_POST['s']) . "%";
        } else {
            $arg = "%";
        }
        $items = $this->search($arg);
        $this->view()->assign('items', $items);
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Modulo e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.chave) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }
    
    private function find($id) {
        return $this->em()->find('\core\model\Modulo', $id);
    }

    public function edit(SGAContext $context) {
        $id = (int) $context->getRequest()->getParameter('id');
        $modulo = $this->find($id);
        if (!$modulo) {
            SGA::redirect('index');
        }
        $this->view()->assign('modulo', $modulo);
        $this->view()->assign('css', $this->getCss($modulo));
        $this->view()->assign('javascript', $this->getJs($modulo));
    }
    
    public function load(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->getRequest()->getParameter('id');
        $type = $context->getRequest()->getParameter('type');
        if (Arrays::contains(array('js', 'css'), $type)) {
            $modulo = $this->find($id);
            if ($modulo) {
                if ($type == 'css') {
                    $response->data = $this->getCss($modulo);
                } else {
                    $response->data = $this->getJs($modulo);
                }
                $response->success = true;
            } else {
                $response->message = _('Módulo inválido');
            }
        } else {
            $response->message = _('Tipo de recurso inválido');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function save(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->getRequest()->getParameter('id');
        $type = $context->getRequest()->getParameter('type');
        $data = $context->getRequest()->getParameter('data');
        if (Arrays::contains(array('js', 'css'), $type)) {
            $modulo = $this->find($id);
            if ($modulo) {
                $filename = ($type == 'css') ? 'css/style.css' : 'js/script.js';
                $filename = $modulo->getFullPath() . DS . $filename;
                if (is_writable($filename)) {
                    file_put_contents($filename, $data);
                    $response->success = true;
                } else {
                    $response->message = _('Permissão negada');
                }
            } else {
                $response->message = _('Módulo inválido');
            }
        } else {
            $response->message = _('Tipo de recurso inválido');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    private function getCss(Modulo $modulo) {
        return file_get_contents($modulo->getFullPath() . DS . 'css/style.css');
    }
    
    private function getJs(Modulo $modulo) {
        return file_get_contents($modulo->getFullPath() . DS . 'js/script.js');
    }
    
}
