<?php
namespace modules\sga\usuarios;

use \Exception;
use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
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
        return;
        $grupos = Arrays::value($_POST, 'grupos', array());
        $servicos = Arrays::value($_POST, 'servicos', array());
        $permissoes = array();
        foreach ($grupos as $g) {
            $aux = explode('@',$g);
            // formato: id_grupo@id_cargo
            $db->inserir_lotacao($model->getId(), $aux[0], $aux[1]);
        }
        if (sizeof($servicos) > 0) {
            $id_uni = SGA::getContext()->getUser()->getUnidade()->getId();
            foreach ($servicos as $s) {
                $db->adicionar_servico_usu($id_uni, $s, $model->getId());
            }
        }
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Usuario e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.login) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }
    
}
