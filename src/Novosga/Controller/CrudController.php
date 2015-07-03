<?php

namespace Novosga\Controller;

use Exception;
use Novosga\Model\SequencialModel;
use Novosga\Context;
use Novosga\Util\Objects;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * CrudController
 * Classe pai para cadastros simples.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class CrudController extends ModuleController
{
    /**
     * @var SequencialModel
     */
    protected $model;

    /**
     * Retorna a entidade do controlador ou null caso nao encontre.
     *
     * @return Model
     */
    protected function findById($id)
    {
        return $this->em()->find(get_class($this->createModel()), $id);
    }

    /**
     * Retorna uma lista das entidades buscadas pelo controlador.
     *
     * @return \Doctrine\ORM\Query
     */
    abstract protected function search($arg);

    /**
     * Retorna um array com a identificacao (name) dos campos obrigatorios.
     *
     * @return array
     */
    abstract protected function requiredFields();

    /**
     * Retorna uma nova instancia da entidade.
     *
     * @return Model
     */
    abstract protected function createModel();

    /**
     * Monta a lista das entidades, podendo filtra-las.
     *
     * @param Novosga\Context $context
     */
    public function index(Context $context)
    {
        $maxResults = 10;
        $page = (int) $context->request()->get('p', 0);
        $search = trim($context->request()->get('s', ''));
        $query = $this->search('%'.strtoupper($search).'%');
        $paginator = new Paginator($query, false);

        $items = $query
                    ->setMaxResults($maxResults)
                    ->setFirstResult($page * $maxResults)
                    ->getResult();

        $total = sizeof($paginator);
        $this->app()->view()->set('search', $search);
        $this->app()->view()->set('items', $items);
        $this->app()->view()->set('total', $total);
        $this->app()->view()->set('page', $page);
        $this->app()->view()->set('pages', ceil($total / $maxResults));
    }

    /**
     * Exibe o formulário de cadastro, tanto novo quanto para alteração.
     *
     * @param Novosga\Context $context
     *
     * @throws \Exception
     */
    public function edit(Context $context, $id = 0)
    {
        $id = (int) $id;
        if ($id > 0) { // editando
            $this->model = $this->findById($id);
            // invalid id
            if (!$this->model) {
                if ($context->getModulo()) {
                    $this->app()->gotoModule();
                } else {
                    $this->app()->gotoHome();
                }
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
                    $value = trim($context->request()->post($field));
                    if (empty($value) && $value !== '0') {
                        throw new \Exception(_('Preencha os campos obrigatórios'));
                    }
                    Objects::set($this->model, $field, $_POST[$field]);
                }
                $id = $context->request()->post('id', 0);
                if ($id > 0) { // editando
                    $this->model->setId($id);
                    $this->doSave($context, $this->model);
                    $message['text'] = _('Registro alterado com sucesso');
                } else { // criando
                    $this->doSave($context, $this->model);
                    $id = $this->model->getId();
                    $redirUrl .= '/'.$id;
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
        $this->app()->view()->set('id', $id);
        $this->app()->view()->set('model', $this->model);
    }

    /**
     * Insere ou atualiza a entidade no banco.
     *
     * @param Novosga\Model\SequencialModel $model
     */
    protected function doSave(Context $context, SequencialModel $model)
    {
        $this->preSave($context, $model);
        if ($model->getId() > 0) {
            $this->em()->merge($model);
        } else {
            $this->em()->persist($model);
        }
        $this->em()->flush();
        $this->postSave($context, $model);
    }

    protected function preSave(Context $context, SequencialModel $model)
    {
    }
    protected function postSave(Context $context, SequencialModel $model)
    {
    }

    public function delete(Context $context, $id)
    {
        $model = $this->findById($id);
        if ($model) {
            try {
                $this->doDelete($context, $model);
                $this->app()->flash('success', _('Registro excluído com sucesso'));
            } catch (Exception $e) {
                try {
                    // se tiver uma transação aberta, dá rollback
                    $this->em()->rollback();
                } catch (\Exception $e2) {
                }
                $this->app()->flash('error', $e->getMessage());
            }
        }
        $this->app()->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Remove a entidade do banco.
     *
     * @param Novosga\Model\SequencialModel $model
     */
    protected function doDelete(Context $context, SequencialModel $model)
    {
        $this->preDelete($context, $model);
        $this->em()->remove($model);
        $this->postDelete($context, $model);
        $this->em()->flush();
    }

    protected function preDelete(Context $context, SequencialModel $model)
    {
    }
    protected function postDelete(Context $context, SequencialModel $model)
    {
    }
}
