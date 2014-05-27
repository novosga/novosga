<?php
namespace modules\sga\modulos;

use \Novosga\SGAContext;
use \Novosga\Util\Arrays;
use \Novosga\Model\Modulo;
use \Novosga\Http\AjaxResponse;
use \Novosga\Controller\ModuleController;
use \Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ModulosController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ModulosController extends ModuleController {
    
    /**
     * Monta a lista das entidades, podendo filtra-las.
     * @param Novosga\SGAContext $context
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
        $this->app()->view()->set('search', $search);
        $this->app()->view()->set('items', $items);
        $this->app()->view()->set('total', $total);
        $this->app()->view()->set('page', $page);
        $this->app()->view()->set('pages', ceil($total / $maxResults));
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Modulo e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.chave) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
    private function find($id) {
        return $this->em()->find('Novosga\Model\Modulo', $id);
    }

    public function edit(SGAContext $context, $id = 0) {
        $id = (int) $id;
        $modulo = $this->find($id);
        if (!$modulo) {
            $this->app()->redirect('index');
        }
        $this->app()->view()->set('modulo', $modulo);
        $this->app()->view()->set('css', $this->getCss($modulo));
        $this->app()->view()->set('javascript', $this->getJs($modulo));
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
        $context->response()->jsonResponse(new AjaxResponse(false, \Novosga\SGA::DEMO_ALERT));
    }
    
    private function getCss(Modulo $modulo) {
        return file_get_contents($modulo->getRealPath() . DS . 'css/style.css');
    }
    
    private function getJs(Modulo $modulo) {
        return file_get_contents($modulo->getrealPath() . DS . 'js/script.js');
    }
    
    protected function preDelete(SGAContext $context, SequencialModel $model) {
        throw new \Exception(\Novosga\SGA::DEMO_ALERT);
    }
    
}
