<?php
namespace home;

use \core\SGAContext;
use \core\business\AcessoBusiness;
use \core\controller\SGAController;
use \core\db\DB;
use \core\Security;
use \core\http\AjaxResponse;

/**
 * HomeController
 * 
 * @author rogeriolino
 *
 */
class HomeController extends SGAController {

    protected function createView() {
        require_once(__DIR__ . '/HomeView.php');
        return new HomeView();
    }
    
    public function index(SGAContext $context) {
        $usuario = $context->getUser();
        $unidade = $usuario->getUnidade();
        // modulos globais
        $this->view()->assign('modulosGlobal', AcessoBusiness::modulos($usuario, \core\model\Modulo::MODULO_GLOBAL));
        // modulos unidades
        if ($unidade) {
            $this->view()->assign('modulosUnidade', AcessoBusiness::modulos($usuario, \core\model\Modulo::MODULO_UNIDADE));
        }
        $this->view()->assign('unidade', $unidade);
        $this->view()->assign('usuario', $usuario);
    }
    
    public function unidade(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->getRequest()->getParameter('unidade');
        try {
            if (!$context->getRequest()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $em = DB::getEntityManager();
            $unidade = $em->find("\core\model\Unidade", $id);
            $context->getUser()->setUnidade($unidade);
            // atualizando a sessao
            $context->setUser($context->getUser());
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function perfil(SGAContext $context) {
        $usuario = $context->getUser();
        if (!$usuario) {
            SGA::redirect('index');
        }
        $salvo = false;
        // se editando
        if ($context->getRequest()->isPost()) {
            // atualizando sessao
            $usuario->setNome($context->getRequest()->getParameter('nome'));
            $usuario->setSobrenome($context->getRequest()->getParameter('sobrenome'));
            $context->setUser($usuario);
            // atualizando banco
            $em = DB::getEntityManager();
            $query = $em->createQuery("
                UPDATE 
                    \core\model\Usuario e 
                SET 
                    e.nome = :nome, e.sobrenome = :sobrenome 
                WHERE 
                    e.id = :id
            ");
            $query->setParameter('id', $usuario->getId());
            $query->setParameter('nome', $usuario->getNome());
            $query->setParameter('sobrenome', $usuario->getSobrenome());
            $query->execute();
            $salvo = true;
        }
        $this->view()->assign('salvo', $salvo);
        $this->view()->assign('usuario', $usuario);
    }
    
    public function alterar_senha(SGAContext $context) {
        $response = new AjaxResponse();
        $usuario = $context->getUser();
        try {
            if (!$usuario) {
                throw new \Exception(_('Nenhum usuário na sessão'));
            }
            $atual = $context->getRequest()->getParameter('atual');
            $senha = $context->getRequest()->getParameter('senha');
            $confirmacao = $context->getRequest()->getParameter('confirmacao');
            $hash = AcessoBusiness::verificaSenha($senha, $confirmacao);
            $em = DB::getEntityManager();
            // verificando senha atual
            $query = $em->createQuery("SELECT u.senha FROM \core\model\Usuario u WHERE u.id = :id");
            $query->setParameter('id', $usuario->getId());
            $rs = $query->getSingleResult();
            if ($rs['senha'] != Security::passEncode($atual)) {
                throw new \Exception(_('Senha atual não confere'));
            }
            // atualizando o banco
            $query = $em->createQuery("UPDATE \core\model\Usuario u SET u.senha = :senha WHERE u.id = :id");
            $query->setParameter('senha', $hash);
            $query->setParameter('id', $usuario->getId());
            $query->execute();
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
