<?php
namespace modules\sga\usuarios;

use \Exception;
use \Novosga\SGAContext;
use \Novosga\Util\Arrays;
use \Novosga\Http\AjaxResponse;
use \Novosga\Model\SequencialModel;
use \Novosga\Model\Usuario;
use \Novosga\Controller\CrudController;

/**
 * UsuariosController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UsuariosController extends CrudController {

    protected function createModel() {
        return new Usuario();
    }
    
    protected function requiredFields() {
        return array('login', 'nome', 'sobrenome');
    }
    
    public function edit(SGAContext $context, $id = 0) {
        parent::edit($context, $id);
        // lotacoes do usuario
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Lotacao e JOIN e.cargo c JOIN e.grupo g WHERE e.usuario = :usuario ORDER BY g.left DESC");
        $query->setParameter('usuario', $this->model->getId());
        $rs = $query->getResult();
        $items = array();
        foreach ($rs as $lotacao) {
            $items[] = array(
                'grupo_id' => $lotacao->getGrupo()->getId(),
                'grupo' => $lotacao->getGrupo()->getNome(),
                'cargo_id' => $lotacao->getCargo()->getId(),
                'cargo' => $lotacao->getCargo()->getNome()
            );
        }
        $this->app()->view()->set('lotacoes', $items);
        // servicos do usuario
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\ServicoUsuario e WHERE e.usuario = :usuario");
        $query->setParameter('usuario', $this->model->getId());
        $rs = $query->getResult();
        $items = array();
        foreach ($rs as $servico) {
            $items[] = array(
                'unidade_id' => $servico->getUnidade()->getId(),
                'unidade' => $servico->getUnidade()->getNome(),
                'servico_id' => $servico->getServico()->getId(),
                'servico' => $servico->getServico()->getNome()
            );
        }
        $this->app()->view()->set('servicos', $items);
        // unidades
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Unidade e ORDER BY e.nome");
        $this->app()->view()->set('unidades', $query->getResult());
        // cargos disponiveis
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Cargo e ORDER BY e.nome");
        $this->app()->view()->set('cargos', $query->getResult());
    }
    
    protected function preSave(SGAContext $context, SequencialModel $model) {
        throw new \Exception(\Novosga\SGA::DEMO_ALERT);
    }
    
    protected function preDelete(SGAContext $context, SequencialModel $model) {
        throw new \Exception(\Novosga\SGA::DEMO_ALERT);
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Usuario e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.login) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
    /**
     * Retorna os grupos disponíveis para serem atribuidos ao usuário. Descartando os grupos com ids informados no parâmetro exceto.
     * @param array $exceto
     */
    private function grupos_disponiveis(array $exceto) {
        // grupos disponiveis (grupos que o usuario nao esta vinculados e que nao sao filhos e nem pai do que esta)
        $query = $this->em()->createQuery("
            SELECT 
                e
            FROM 
                Novosga\Model\Grupo e 
            WHERE 
                NOT EXISTS (
                    SELECT 
                        g2.id 
                    FROM 
                        Novosga\Model\Grupo g2 
                    WHERE 
                        (
                            g2.left <= e.left AND g2.right >= e.right OR
                            g2.left >= e.left AND g2.right <= e.right 
                        )
                        AND g2.id IN (:exceto))
            ORDER BY 
                e.left, e.nome
        ");
        $query->setParameter('exceto', $exceto);
        return $query->getResult();
    }
    
    /**
     * Retorna os grupos disponíveis para serem atribuidos ao usuário. Descartando os grupos com ids informados no parâmetro exceto.
     * @param Novosga\SGAContext $context
     */
    public function grupos(SGAContext $context) {
        $exceto = $context->request()->getParameter('exceto');
        $exceto = Arrays::valuesToInt(explode(',', $exceto));
        $response = new AjaxResponse(true);
        $grupos = $this->grupos_disponiveis($exceto);
        foreach ($grupos as $g) {
            $response->data[] = array('id' => $g->getId(), 'nome' => $g->getNome());
        }
        $context->response()->jsonResponse($response);
    }

    /**
     * Retorna as permissões do cargo informado
     * @param Novosga\SGAContext $context
     */
    public function permissoes_cargo(SGAContext $context) {
        $response = new AjaxResponse(true);
        $id = (int) $context->request()->getParameter('cargo');
        $query = $this->em()->createQuery("SELECT m.nome FROM Novosga\Model\Permissao e JOIN e.modulo m WHERE e.cargo = :cargo ORDER BY m.nome");
        $query->setParameter('cargo', $id);
        $response->data = $query->getResult();
        $context->response()->jsonResponse($response);
    }

    /**
     * Retorna os serviços habilitados na unidade informada. Descartando os serviços com ids informados no parâmetro exceto
     * @param Novosga\SGAContext $context
     */
    public function servicos_unidade(SGAContext $context) {
        $response = new AjaxResponse(true);
        $id = (int) $context->request()->getParameter('unidade');
        $exceto = $context->request()->getParameter('exceto');
        $exceto = Arrays::valuesToInt(explode(',', $exceto));
        $query = $this->em()->createQuery("
            SELECT 
                s.id, e.nome 
            FROM 
                Novosga\Model\ServicoUnidade e 
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
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Altera a senha do usuario que está sendo editado
     * @param Novosga\SGAContext $context
     */
    public function alterar_senha(SGAContext $context) {
        $context->response()->jsonResponse(new AjaxResponse(false, \Novosga\SGA::DEMO_ALERT));
    }
    
}
