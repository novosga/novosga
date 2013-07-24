<?php
namespace painel;

use \core\SGAContext;
use \core\controller\SGAController;
use \core\db\DB;
use \painel\protocol\ProtocolFactory;

/**
 * PainelController
 * 
 * @author rogeriolino
 *
 */
class PainelController extends SGAController {

    protected function createView() {
        require_once(__DIR__ . '/PainelView.php');
        return new PainelView();
    }
    
    public function index(SGAContext $context) {
        $context->getResponse()->setRenderView(false);
        $unidades = DB::getEntityManager()->createQuery("SELECT e FROM \core\model\Unidade e WHERE e.status = 1 ORDER BY e.nome")->getResult();
        $this->view()->assign('unidades', $unidades);
    }
    
    public function servicos(SGAContext $context) {
        $version = (int) $context->getRequest()->getParameter('version');
        $unidade = (int) $context->getRequest()->getParameter('unidade');
        $query = DB::getEntityManager()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade AND e.status = 1 ORDER BY e.nome");
        $query->setParameter(':unidade', $unidade);
        echo ProtocolFactory::create($version)->encodeServicos($query->getResult());
        exit();
    }
    
    public function unidades(SGAContext $context) {
        $version = (int) $context->getRequest()->getParameter('version');
        $query = DB::getEntityManager()->createQuery("SELECT e FROM \core\model\Unidade e ORDER BY e.nome");
        echo ProtocolFactory::create($version)->encodeUnidades($query->getResult());
        exit();
    }
    
    public function painel_web_update(SGAContext $context) {
        $em = DB::getEntityManager();
        $unidade = (int) $context->getRequest()->getParameter('unidade');
        $servicos = $context->getRequest()->getParameter('servicos');
        if (empty($servicos)) {
            $servicos = '0';
        }
        $servicos = explode(',', $servicos);
        // pega as ultimas 5 senhas a serem exibidas
        $query = $em->createQuery("SELECT e FROM \core\model\PainelSenha e WHERE e.unidade = :unidade AND e.servico IN (:servicos) ORDER BY e.id DESC");
        $query->setParameter('unidade', $unidade);
        $query->setParameter('servicos', $servicos);
        $query->setMaxResults(5);
        $rs = $query->getResult();
        $response = new \core\http\AjaxResponse(true);
        $response->data = array();
        foreach ($rs as $senha) {
            $response->data[] = $senha->toArray();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function painel_web_servicos(SGAContext $context) {
        $em = DB::getEntityManager();
        $unidade = (int) $context->getRequest()->getParameter('unidade');
        $query = $em->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade AND e.status = 1");
        $query->setParameter('unidade', $unidade);
        $rs = $query->getResult();
        $response = new \core\http\AjaxResponse(true);
        $response->data = array();
        foreach ($rs as $s) {
            $response->data[] = array(
                'id' => $s->getServico()->getId(),
                'nome' => $s->getNome()
            );
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
