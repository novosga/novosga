<?php

namespace modules\sga\modulos;

use Exception;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Novosga\Service\ModuloService;
use Novosga\Context;
use Novosga\Controller\ModuleController;
use Novosga\Http\JsonResponse;
use Novosga\Model\Modulo;
use Novosga\Util\Arrays;
use Novosga\Util\FileUtils;

/**
 * ModulosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ModulosController extends ModuleController
{
    /**
     * Monta a lista das entidades, podendo filtra-las.
     *
     * @param Context $context
     */
    public function index(Context $context)
    {
        $maxResults = 10;
        $page = (int) $context->request()->get('p', 0);
        $search = trim($context->request()->get('s', ''));
        $query = $this->search('%'.strtoupper($search).'%');
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

    protected function search($arg)
    {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Modulo e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.chave) LIKE :arg");
        $query->setParameter('arg', $arg);

        return $query;
    }

    private function find($id)
    {
        return $this->em()->find('Novosga\Model\Modulo', $id);
    }

    public function edit(Context $context, $id = 0)
    {
        if ($context->request()->isPost()) {
        } else {
            $id = (int) $id;
            $modulo = $this->find($id);
            if ($modulo) {
                $this->app()->view()->set('modulo', $modulo);
                $this->app()->view()->set('css', $this->getCss($modulo));
                $this->app()->view()->set('javascript', $this->getJs($modulo));
            }
        }
    }

    public function load(Context $context)
    {
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

    public function save(Context $context)
    {
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
            $filename = ($type == 'css') ? '/public/css/style.css' : '/public/js/script.js';
            $filename = $modulo->getRealPath().$filename;
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

    public function install(Context $context)
    {
        $response = new JsonResponse();
        try {
            // file upload handling
            $ext = 'zip';
            $fu = new FileUpload('uploadfile');
            $result = $fu->handleUpload(NOVOSGA_CACHE, array($ext));
            if (!$result) {
                throw new Exception($fu->getErrorMsg());
            }
            // install module
            $service = new ModuloService($this->em());
            $service->extractAndInstall($fu->getSavedFile(), $fu->getExtension());
            FileUtils::rm($fu->getSavedFile());
            // response
            $response->success = true;
            $response->message = _('Módulo instalado com sucesso');
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function delete(Context $context, $id)
    {
        $modulo = $this->find($id);
        $service = new ModuloService($this->em());
        $service->uninstall($modulo->getChave());
        $this->app()->redirect("{$context->request()->getRootUri()}/modules/sga.modulos");
    }

    public function toggle(Context $context)
    {
        $modulo = $this->find($context->request()->post('id'));
        if ($modulo) {
            $modulo->setStatus(!$modulo->getStatus());
            $this->em()->merge($modulo);
            $this->em()->flush();
        }

        return new JsonResponse(true);
    }

    private function getCss(Modulo $modulo)
    {
        return file_get_contents($modulo->getRealPath().'/public/css/style.css');
    }

    private function getJs(Modulo $modulo)
    {
        return file_get_contents($modulo->getrealPath().'/public/js/script.js');
    }
}
