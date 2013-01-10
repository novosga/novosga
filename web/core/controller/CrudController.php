<?php
namespace core\controller;

use \Exception;
use \core\SGA;
use \core\db\DB;
use core\view\CrudView;
use \core\model\SequencialModel;
use \core\controller\ModuleController;
use \core\SGAContext;
use \core\util\Arrays;
use \core\util\Objects;

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
     * @return CrudView
     */
    protected function createView() {
        return new CrudView($this->title, $this->subtitle);
    }
    
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
    
    /**
     * Exibe o formulário de cadastro, tanto novo quanto para alteração
     * @param \core\SGAContext $context
     * @throws \Exception
     */
    public function edit(SGAContext $context) {
        $id = (int) Arrays::value($_GET, 'id');
        if ($id > 0) { // editando
            $this->model = $this->findById($id);
            // invalid id
            if (!$this->model) {
                SGA::redirect(array(SGA::K_MODULE => $context->getModulo()->getChave()));
            }
        } else {
            $this->model = $this->createModel();
        }
        $message = null;
        if ($context->getRequest()->isPost()) {
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
                    $message = array('success' => true, 'message' => _('Registro alterado com sucesso'));
                } else { // criando
                    $this->doSave($context, $this->model);
                    $id = $this->model->getId();
                    if ($id > 0) {
                        $message = array('success' => true, 'message' => _('Novo registro adicionado com sucesso'));
                    } else {
                        $message = array('success' => false, 'message' => _('Erro ao salvar o novo registro. Favor tentar novamente'));
                    }
                }
            } catch (\Exception $e) {
                $message = array('success' => false, 'message' => $e->getMessage());
            }
        }
        $this->view()->assign('id', $id);
        $this->view()->assign('message', $message);
        $this->view()->assign('model', $this->model);
    }
    
    /**
     * Insere ou atualiza a entidade no banco
     * @param \core\model\SequencialModel $model
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
                $this->view()->addMessage($e->getMessage(), 'error');
            }
        }
        SGA::redirect($_SERVER['HTTP_REFERER']);
    }
    
    /**
     * Insere ou atualiza a entidade no banco
     * @param \core\model\SequencialModel $model
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
