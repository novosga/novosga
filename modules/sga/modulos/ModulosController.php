<?php
namespace modules\sga\modulos;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Novosga\Context;
use Novosga\Controller\ModuleController;
use Novosga\Http\JsonResponse;
use Novosga\Model\Modulo;
use Novosga\Util\Arrays;

/**
 * ModulosController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ModulosController extends ModuleController {
    
    /**
     * Monta a lista das entidades, podendo filtra-las.
     * @param Context $context
     */
    public function index(Context $context) {
        $maxResults = 10;
        $page = (int) $context->request()->get('p', 0);
        $search = trim($context->request()->get('s', ''));
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

    public function edit(Context $context, $id = 0) {
        $id = (int) $id;
        $modulo = $this->find($id);
        if (!$modulo) {
            $this->app()->redirect('index');
        }
        $this->app()->view()->set('modulo', $modulo);
        $this->app()->view()->set('css', $this->getCss($modulo));
        $this->app()->view()->set('javascript', $this->getJs($modulo));
    }
    
    public function load(Context $context) {
        $response = new JsonResponse();
        try {
            $id = (int) $context->request()->get('id');
            $type = $context->request()->get('type');
            if (!Arrays::contains(array('js', 'css'), $type)) {
                throw new Exception(_('Tipo de recurso inválido'));
            }
            $modulo = $this->find($id);
            if (!$modulo) {
                throw new Exception(_('Módulo inválido'));
            }
            $response->data = ($type == 'css') ? $this->getCss($modulo) : $this->getJs($modulo);
            $response->success = true;
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
    public function save(Context $context) {
        $response = new JsonResponse();
        try {
            $id = (int) $context->request()->post('id');
            $type = $context->request()->post('type');
            $data = $context->request()->post('data');
            if (Arrays::contains(array('js', 'css'), $type)) {
                throw new Exception(_('Tipo de recurso inválido'));
            }
            $modulo = $this->find($id);
            if (!$modulo) {
                throw new Exception(_('Módulo inválido'));
            }
            $filename = ($type == 'css') ? 'css/style.css' : 'js/script.js';
            $filename = $modulo->getRealPath() . DS . $filename;
            if (!is_writable($filename)) {
                throw new Exception(_('Permissão negada'));
            }
            file_put_contents($filename, $data);
            $response->success = true;
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
    private function getCss(Modulo $modulo) {
        return file_get_contents($modulo->getRealPath() . DS . 'css/style.css');
    }
    
    private function getJs(Modulo $modulo) {
        return file_get_contents($modulo->getrealPath() . DS . 'js/script.js');
    }
    
}
