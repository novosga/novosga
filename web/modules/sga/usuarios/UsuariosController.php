<?php
namespace modules\sga\usuarios;

use \Exception;
use \core\db\DB;
use \core\SGAContext;
use \core\util\Arrays;
use \core\http\AjaxResponse;
use \core\Security;
use \core\model\SequencialModel;
use \core\model\Usuario;
use \core\controller\CrudController;

/**
 * UsuariosController
 *
 * @author rogeriolino
 */
class UsuariosController extends CrudController {

    protected function createModel() {
        return new Usuario();
    }
    
    protected function requiredFields() {
        return array('login', 'nome', 'sobrenome');
    }
    
    public function edit(SGAContext $context) {
        parent::edit($context);
        $lotacoes = array();
        if ($this->model->getId() > 0) {
            $query = DB::getEntityManager()->createQuery("
                SELECT 
                    e 
                FROM 
                    \core\model\Lotacao e 
                    JOIN e.cargo c 
                    JOIN e.grupo g 
                WHERE 
                    e.usuario = :usuario 
                ORDER BY 
                    g.left DESC
            ");
            $query->setParameter('usuario', $this->model->getId());
            $lotacoes = $query->getResult();
        }
        $this->view()->assign('lotacoes', $lotacoes);
        // grupos disponiveis
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Grupo e WHERE e.id NOT IN (SELECT g.id FROM \core\model\Lotacao l JOIN l.grupo g WHERE l.usuario = :usuario) ORDER BY e.nome");
        $query->setParameter('usuario', $this->model->getId());
        $this->view()->assign('grupos', $query->getResult());
        // cargos disponiveis
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Cargo e WHERE e.id NOT IN (SELECT c.id FROM \core\model\Lotacao l JOIN l.cargo c WHERE l.usuario = :usuario) ORDER BY e.nome");
        $query->setParameter('usuario', $this->model->getId());
        $this->view()->assign('cargos', $query->getResult());
        // servicos do usuario
        $query = $this->em()->createQuery("SELECT e FROM \core\model\ServicoUsuario e WHERE e.usuario = :usuario");
        $query->setParameter('usuario', $this->model->getId());
        $this->view()->assign('servicos', $query->getResult());
        // unidades
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e ORDER BY e.nome");
        $this->view()->assign('unidades', $query->getResult());
    }

    public function permissoes_cargo(SGAContext $context) {
        $response = new AjaxResponse(true);
        $id = (int) $context->getRequest()->getParameter('cargo');
        $query = $this->em()->createQuery("SELECT m.nome FROM \core\model\Permissao e JOIN e.modulo m WHERE e.cargo = :cargo ORDER BY m.nome");
        $query->setParameter('cargo', $id);
        $response->data = $query->getResult();
        $context->getResponse()->jsonResponse($response);
    }

    public function servicos_unidade(SGAContext $context) {
        $response = new AjaxResponse(true);
        $id = (int) $context->getRequest()->getParameter('unidade');
        $exceto = $context->getRequest()->getParameter('exceto');
        $exceto = Arrays::valuesToInt(explode(',', $exceto));
        $query = $this->em()->createQuery("
            SELECT 
                s.id, e.nome 
            FROM 
                \core\model\ServicoUnidade e 
                JOIN e.unidade u 
                JOIN e.servico s 
            WHERE 
                e.status = 1 AND 
                u = :unidade AND
                s.id NOT IN (:exceto)
            ORDER BY 
                e.nome
        ");
        $query->setParameter('unidade', $id);
        $query->setParameter('exceto', $exceto);
        $response->data = $query->getResult();
        $context->getResponse()->jsonResponse($response);
    }
    
    protected function preSave(SGAContext $context, SequencialModel $model) {
        if ($model->getId() == 0) {
            // para novos usuarios, tem que informar a senha
            $login = Arrays::value($_POST, 'login');
            if (!ctype_alnum($login)) {
                throw new Exception(_('O login deve conter somente letras e números.'));
            }
            if (strlen($login) < 5 || strlen($login) > 20) {
                throw new Exception(_('O login deve possuir entre 5 e 20 caracteres (letras ou números).'));
            }
            $senha = Arrays::value($_POST, 'senha');
            $senha2 = Arrays::value($_POST, 'senha2');
            if (strlen($senha) < 6) {
                throw new Exception(_('A senha deve possuir no mínimo 6 caracteres.'));
            }
            if ($senha != $senha2) {
                throw new Exception(_('A confirmação de senha não confere com a senha.'));
            }
            $model->setStatus(1);
            $model->setSenha(Security::passEncode($senha));
        } else {
            $model->setStatus((int) Arrays::value($_POST, 'status'));
        }
        // verificando novo login ou alteracao
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM \core\model\Usuario e WHERE e.login = :login AND e.id != :id");
        $query->setParameter('login', $model->getLogin());
        $query->setParameter('id', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total']) {
            throw new \Exception(_('O login informado já está cadastrado para outro usuário.'));
        }
        $model->setSessionId('');
    }
    
    protected function postSave(SGAContext $context, SequencialModel $model) {
        $conn = $this->em()->getConnection();
        // lotacoes - atualizando permissoes do cargo
        $query = $this->em()->createQuery("DELETE FROM \core\model\Lotacao e WHERE e.usuario = :usuario");
        $query->setParameter('usuario', $model->getId());
        $query->execute();
        $lotacoes = Arrays::value($_POST, 'lotacoes', array());
        if (!empty($lotacoes)) {
            $stmt = $conn->prepare("INSERT INTO usu_grup_cargo (id_grupo, id_cargo, id_usu) VALUES (:grupo, :cargo, :usuario)");
            foreach ($lotacoes as $lotacao) {
                $value = explode(',', $lotacao);
                $stmt->bindValue('grupo', $value[0], \PDO::PARAM_INT);
                $stmt->bindValue('cargo', $value[1], \PDO::PARAM_INT);
                $stmt->bindValue('usuario', $model->getId(), \PDO::PARAM_INT);
                $stmt->execute();
            }
        }
        // servicos
        $query = $this->em()->createQuery("DELETE FROM \core\model\ServicoUsuario e WHERE e.usuario = :usuario");
        $query->setParameter('usuario', $model->getId());
        $query->execute();
        $servicos = Arrays::value($_POST, 'servicos', array());
        if (!empty($servicos)) {
            $stmt = $conn->prepare("INSERT INTO usu_serv (id_uni, id_serv, id_usu) VALUES (:unidade, :servico, :usuario)");
            foreach ($servicos as $servico) {
                $value = explode(',', $servico);
                $stmt->bindValue('unidade', $value[0], \PDO::PARAM_INT);
                $stmt->bindValue('servico', $value[1], \PDO::PARAM_INT);
                $stmt->bindValue('usuario', $model->getId(), \PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Usuario e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.login) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }
    
}
