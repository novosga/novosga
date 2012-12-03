<?php
namespace modules\sga\usuarios;

use \core\SGA;
use \core\util\Arrays;
use \core\model\SequencialModel;
use \core\model\Usuario;
use \core\controller\CrudController;

/**
 * UsuariosController
 *
 * @author rogeriolino
 */
class UsuariosController extends CrudController {
    
    public function __construct() {
        $this->title = _('Usuários');
        $this->subtitle = _('Gerencie os usuários do SGA');
    }

    protected function createModel() {
        return new Usuario();
    }
    
    protected function requiredFields() {
        return array('login', 'nome', 'sobrenome');
    }

    protected function preSave(SequencialModel $model) {
        if ($model->getId() == 0) {
            // para novos usuarios, tem que informar a senha
            $senha = Arrays::value($_POST, 'senha');
            $senha2 = Arrays::value($_POST, 'senha2');
            if (empty($senha)) {
                throw new Exception(_('Preencha a senha corretamente.'));
            }
            if ($senha != $senha2) {
                throw new Exception(_('A confirmação de senha não confere com a senha.'));
            } else if (!ctype_alnum($senha)) {
                throw new Exception(_('A senha deve possuir somente letras e números.'));
            } else if (strlen($senha) < 6) {
                throw new Exception(_('A senha deve possuir no mínimo 6 caracteres.'));
            }
        }
        // verificando novo login ou alteracao
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM \core\model\Usuario e WHERE e.login = :login AND e.id != :id");
        $query->setParameter('login', $model->getLogin());
        $query->setParameter('id', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total']) {
            throw new \Exception(_('O login informado já está cadastrado para outro usuário.'));
        }
    }
    
    protected function postSave(SequencialModel $model) {
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
