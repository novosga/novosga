<?php
namespace novosga\controller;

use \Exception;
use \novosga\SGA;
use \novosga\db\DB;
use \novosga\view\CrudView;
use \novosga\model\SequencialModel;
use \novosga\controller\ModuleController;
use \novosga\SGAContext;
use \novosga\util\Arrays;
use \novosga\util\Objects;

/**
 * CrudController
 * Classe pai para cadastros simples
 *
 * @author rogeriolino
 */
abstract class CrudController extends ModuleController {

    /**
     * @var SequencialModel
     */
    protected $model;
    
    /**
     * Retorna a entidade do controlador ou null caso nao encontre
     * @return Model
     */
    protected function findById($id) {
        return DB::getEntityManager()->find(get_class($this->createModel()), $id);
    }
 
    /**
     * Retorna uma lista das entidades buscadas pelo controlador
     * @return array
     */
    protected abstract function search($arg);
    
    /**
     * Retorna um array com a identificacao (name) dos campos obrigatorios
     * @return array 
     */
    protected abstract function requiredFields();
    
    /**
     * Retorna uma nova instancia da entidade
     * @return Model
     */
    protected abstract function createModel();
    
    /**
     * Monta a lista das entidades, podendo filtra-las.
     * @param novosga\SGAContext $context
     */
    public function index(SGAContext $context) {
        $search = (isset($_GET['s'])) ? trim($_GET['s']) : '';
        $items = $this->search("%". strtoupper($search) . "%");
        $this->app()->view()->assign('search', $search);
        $this->app()->view()->assign('items', $items);
    }
    
    /**
     * Exibe o formulário de cadastro, tanto novo quanto para alteração
     * @param novosga\SGAContext $context
     * @throws \Exception
     */
    public function edit(SGAContext $context, $id = 0) {
        $id = (int) $id;
        if ($id > 0) { // editando
            $this->model = $this->findById($id);
            // invalid id
            if (!$this->model) {
                SGA::redirect(array(SGA::K_MODULE => $context->getModulo()->getChave()));
            }
        } else {
            $this->model = $this->createModel();
        }
        if ($context->request()->isPost()) {
            $redirUrl = $_SERVER['HTTP_REFERER'];
            $message = array('success' => true, 'text' => '');
            $requiredFields = $this->requiredFields();
            try {
                foreach ($requiredFields as $field) {
                    $value = trim(Arrays::value($_POST, $field));
                    if (empty($value) && $value !== '0') {
                        throw new \Exception(_('Preencha os campos obrigatórios'));
                    }
                    Objects::set($this->model, $field, $_POST[$field]);
                }
                $id = Arrays::value($_POST, 'id', 0);
                if ($id > 0) { // editando
                    $this->model->setId($id);
                    $this->doSave($context, $this->model);
                    $message['text'] = _('Registro alterado com sucesso');
                } else { // criando
                    $this->doSave($context, $this->model);
                    $id = $this->model->getId();
                    $redirUrl .= '/' . $id;
                    if ($id > 0) {
                        $message['text'] = _('Novo registro adicionado com sucesso');
                    } else {
                        $message['text'] = _('Erro ao salvar o novo registro. Favor tentar novamente');
                        $message['success'] = false;
                    }
                }
            } catch (\Exception $e) {
                $message['text'] = $e->getMessage();
                $message['success'] = false;
            }
            if (!empty($message['text'])) {
                $this->app()->flash($message['success'] ? 'success' : 'error', $message['text']);
            }
            $this->app()->redirect($redirUrl);
        }
        $this->app()->view()->assign('id', $id);
        $this->app()->view()->assign('model', $this->model);
    }
    
    /**
     * Insere ou atualiza a entidade no banco
     * @param novosga\model\SequencialModel $model
     */
    protected function doSave(SGAContext $context, SequencialModel $model) {
        $this->preSave($context, $model);
        if ($model->getId() > 0) {
            $this->em()->merge($model);
        } else {
            $this->em()->persist($model);
        }
        $this->em()->flush();
        $this->postSave($context, $model);
    }
    
    protected function preSave(SGAContext $context, SequencialModel $model) {}
    protected function postSave(SGAContext $context, SequencialModel $model) {}
    
    public function delete(SGAContext $context) {
        $id = (int) Arrays::value($_POST, 'id');
        $model = $this->findById($id);
        if ($model) {
            try {
                $this->doDelete($context, $model);
            } catch (Exception $e) {
                try {
                    // se tiver uma transação aberta, dá rollback
                    $this->em()->rollback();
                } catch (\Exception $e2) {}
                $this->app()->flash('error', $e->getMessage());
            }
        }
        SGA::redirect($_SERVER['HTTP_REFERER']);
    }
    
    /**
     * Remove a entidade do banco
     * @param novosga\model\SequencialModel $model
     */
    protected function doDelete(SGAContext $context, SequencialModel $model) {
        $this->preDelete($context, $model);
        $this->em()->remove($model);
        $this->postDelete($context, $model);
        $this->em()->flush();
    }
    
    protected function preDelete(SGAContext $context, SequencialModel $model) {}
    protected function postDelete(SGAContext $context, SequencialModel $model) {}
    
}
