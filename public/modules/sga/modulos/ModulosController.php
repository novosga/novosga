<?php
namespace modules\sga\modulos;

use \novosga\SGAContext;
use \novosga\util\Arrays;
use \novosga\model\Modulo;
use \novosga\http\AjaxResponse;
use \novosga\controller\ModuleController;
use \Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ModulosController
 *
 * @author rogeriolino
 */
class ModulosController extends ModuleController {
    
    /**
     * Monta a lista das entidades, podendo filtra-las.
     * @param novosga\SGAContext $context
     */
    public function index(SGAContext $context) {
        $maxResults = 10;
        $page = (int) Arrays::value($_GET, 'p', 0);
        $search = trim(Arrays::value($_GET, 's', ''));
        $query = $this->search("%". strtoupper($search) . "%");
        $query->setMaxResults($maxResults);
        $query->setFirstResult($page * $maxResults);
        $items = new Paginator($query);
        $total = count($items);
        $this->app()->view()->assign('search', $search);
        $this->app()->view()->assign('items', $items);
        $this->app()->view()->assign('total', $total);
        $this->app()->view()->assign('page', $page);
        $this->app()->view()->assign('pages', ceil($total / $maxResults));
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\Modulo e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.chave) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
    private function find($id) {
        return $this->em()->find('novosga\model\Modulo', $id);
    }

    public function edit(SGAContext $context, $id = 0) {
        $id = (int) $id;
        $modulo = $this->find($id);
        if (!$modulo) {
            $this->app()->redirect('index');
        }
        $this->app()->view()->assign('modulo', $modulo);
        $this->app()->view()->assign('css', $this->getCss($modulo));
        $this->app()->view()->assign('javascript', $this->getJs($modulo));
    }
    
    public function load(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->request()->getParameter('id');
        $type = $context->request()->getParameter('type');
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
        $context->response()->jsonResponse($response);
    }
    
    public function save(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->request()->getParameter('id');
        $type = $context->request()->getParameter('type');
        $data = $context->request()->getParameter('data');
        if (Arrays::contains(array('js', 'css'), $type)) {
            $modulo = $this->find($id);
            if ($modulo) {
                $filename = ($type == 'css') ? 'css/style.css' : 'js/script.js';
                $filename = $modulo->getRealPath() . DS . $filename;
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
        $context->response()->jsonResponse($response);
    }
    
    private function getCss(Modulo $modulo) {
        return file_get_contents($modulo->getRealPath() . DS . 'css/style.css');
    }
    
    private function getJs(Modulo $modulo) {
        return file_get_contents($modulo->getrealPath() . DS . 'js/script.js');
    }
    
}
