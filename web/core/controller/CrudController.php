<?php
namespace core\controller;

use \core\SGA;
use \core\db\DB;
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
     * Insere ou atualiza a entidade no banco
     * @param \core\model\SequencialModel $model
     */
    private function save(SequencialModel $model) {
        $this->preSave($model);
        if ($model->getId() > 0) {
            $this->em()->merge($model);
        } else {
            $this->em()->persist($model);
        }
        $this->postSave($model);
        $this->em()->flush();
    }
    
    protected function preSave(SequencialModel $model) {}
    protected function postSave(SequencialModel $model) {}
    
    public function index(SGAContext $context) {
        $context = SGA::getContext();
        if (isset($_POST['s'])) {
            $arg = "%" . $_POST['s'] . "%";
        } else {
            $arg = "%";
        }
        $items = $this->search($arg);
        $this->view()->assign('items', $items);
    }
    
    public function edit(SGAContext $context) {
        $id = (int) Arrays::value($_GET, 'id');
        if ($id > 0) { // editando
            $this->model = $this->findById($id);
            // invalid id
            if (!$this->model) {
                SGA::redirect(array(SGA::K_MODULE => $context->getModule()->getChave()));
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
                    if (empty($value)) {
                        throw new \Exception(_('Preencha os campos obrigatórios'));
                    }
                    Objects::set($this->model, $field, $_POST[$field]);
                }

                $id = Arrays::value($_POST, 'id', 0);
                if ($id > 0) { // editando
                    $this->model->setId($id);
                    $this->save($this->model);
                    $message = array('success' => true, 'message' => _('Registro alterado com sucesso'));
                } else { // criando
                    $this->save($this->model);
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
    
}
