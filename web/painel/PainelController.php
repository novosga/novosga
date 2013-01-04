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
    }
    
    public function servicos(SGAContext $context) {
        $version = (int) $context->getRequest()->getParameter('version');
        $unidade = (int) $context->getRequest()->getParameter('unidade');
        $query = DB::getEntityManager()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade ORDER BY e.nome");
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
    
}
