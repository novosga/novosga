<?php
namespace modules\sga\servicos;

use \novosga\SGAContext;
use \novosga\util\Arrays;
use \novosga\model\SequencialModel;
use \novosga\model\Servico;
use \novosga\controller\CrudController;

/**
 * ServicosController
 *
 * @author rogeriolino
 */
class ServicosController extends CrudController {
    
    protected function createModel() {
        return new Servico();
    }
    
    protected function requiredFields() {
        return array('nome', 'descricao', 'status');
    }
    
    /**
     * Insere ou atualiza a entidade no banco
     * @param novosga\model\SequencialModel $model
     */
    protected function doSave(SGAContext $context, SequencialModel $model) {
        $this->preSave($context, $model);
        if ($model->getId() > 0) {
            // #51 problema ao insertir ou atualizar valor nulo usando sql server no linux
            if (\novosga\Config::DB_TYPE === 'mssql' && !$model->getMestre()) {
                $stmt = $this->em()->getConnection()->prepare('UPDATE servicos SET nm_serv = ?, desc_serv = ?, stat_serv = ?, id_macro = null WHERE id_serv = ?');
                $stmt->bindValue(1, $model->getNome(), 'string');
                $stmt->bindValue(2, $model->getDescricao(), 'string');
                $stmt->bindValue(3, $model->getStatus(), 'integer');
                $stmt->bindValue(4, $model->getId(), 'integer');
                $stmt->execute();
            } else {
                $this->em()->merge($model);
                $this->em()->flush();
            }
        } else {
            // #51 problema ao insertir ou atualizar valor nulo usando sql server no linux
            if (\novosga\Config::DB_TYPE === 'mssql' && !$model->getMestre()) {
                $stmt = $this->em()->getConnection()->prepare('INSERT INTO servicos (nm_serv, desc_serv, stat_serv) VALUES (?, ?, ?)');
                $stmt->bindValue(1, $model->getNome(), 'string');
                $stmt->bindValue(2, $model->getDescricao(), 'string');
                $stmt->bindValue(3, $model->getStatus(), 'integer');
                $stmt->execute();
                $model->setId($this->em()->getConnection()->lastInsertId());
            } else {
                $this->em()->persist($model);
                $this->em()->flush();
            }
        }
        $this->postSave($context, $model);
    }

    protected function preSave(SGAContext $context, SequencialModel $model) {
        $id_macro = (int) Arrays::value($_POST, 'id_macro');
        $macro = $this->em()->find("novosga\model\Servico", $id_macro);
        $model->setMestre($macro);
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                novosga\model\Servico e 
                LEFT JOIN e.mestre m
            WHERE 
                UPPER(e.nome) LIKE :arg OR 
                UPPER(e.descricao) LIKE :arg
            ORDER BY
                e.nome
        ");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }

    public function edit(SGAContext $context, $id = 0) {
        parent::edit($context, $id);
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\Servico e WHERE e.mestre IS NULL AND e.id != :id ORDER BY e.nome ASC");
        $query->setParameter('id', $this->model->getId());
        $this->app()->view()->assign('macros', $query->getResult());
    }
    
    /**
     * Verifica se já existe unidade usando o serviço.
     * @param novosga\model\SequencialModel $model
     */
    protected function preDelete(SGAContext $context, SequencialModel $model) {
        $error = _('Já existem atendimentos para o serviço que está tentando remover');
        // quantidade de atendimentos do servico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM novosga\model\Atendimento e JOIN e.servicoUnidade su WHERE su.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // quantidade de atendimentos do servico, no historico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM novosga\model\ViewAtendimento e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // apagando vinculo com as unidades
        $this->em()->beginTransaction();
        $query = $this->em()->createQuery("DELETE FROM novosga\model\ServicoUnidade e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $query->execute();
    }
    
    protected function postDelete(SGAContext $context, SequencialModel $model) {
        $this->em()->commit();
    }
    
}
