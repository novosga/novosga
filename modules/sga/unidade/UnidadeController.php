<?php
namespace modules\sga\unidade;

use Exception;
use Novosga\Business\AtendimentoBusiness;
use Novosga\Context;
use Novosga\Controller\ModuleController;
use Novosga\Http\JsonResponse;

/**
 * UnidadeController
 * 
 * Controlador do módulo de configuração da unidade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadeController extends ModuleController {
    
    public function index(Context $context) {
        $unidade = $context->getUnidade();
        $this->app()->view()->set('unidade', $unidade);
        if ($unidade) {
            $locais = $this->em()->getRepository('Novosga\Model\Local')->findAll();
            if (sizeof($locais)) {
                $local = $locais[0];
                // atualizando relacionamento entre unidade e servicos mestre
                $conn = $this->em()->getConnection();
                $conn->executeUpdate("
                    INSERT INTO uni_serv 
                        (unidade_id, servico_id, local_id, nome, sigla, status, peso)
                    SELECT 
                        :unidade, id, :local, nome, 'A', 0, peso 
                    FROM 
                        servicos 
                    WHERE 
                        macro_id IS NULL AND
                        id NOT IN (SELECT servico_id FROM uni_serv WHERE unidade_id = :unidade)
                ", array('unidade' => $unidade->getId(), 'local' => $local->getId()));
                // todos servicos da unidade
                $query = $this->em()->createQuery("
                    SELECT 
                        e 
                    FROM 
                        Novosga\Model\ServicoUnidade e 
                    WHERE 
                        e.unidade = :unidade 
                    ORDER BY 
                        e.nome
                ");
                $query->setParameter('unidade', $unidade->getId());
                $this->app()->view()->set('servicos', $query->getResult());
                // locais disponiveis
                $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Local e ORDER BY e.nome");
                $this->app()->view()->set('locais', $query->getResult());
            }
        }
    }
    
    public function update_impressao(Context $context) {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $impressao = (int) $context->request()->post('impressao');
            $mensagem = $context->request()->post('mensagem', '');
            $unidade = $context->getUser()->getUnidade();
            $query = $this->em()->createQuery("UPDATE Novosga\Model\Unidade e SET e.statusImpressao = :status, e.mensagemImpressao = :mensagem WHERE e.id = :unidade");
            $query->setParameter('status', $impressao);
            $query->setParameter('mensagem', $mensagem);
            $query->setParameter('unidade', $unidade->getId());
            if ($query->execute()) {
                // atualizando sessao
                $unidade = $this->em()->find('Novosga\Model\Unidade', $unidade->getId());
                $context->setUnidade($unidade);
                $response->success = true;
            }
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
    private function change_status(Context $context, $status) {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $servico_id = (int) $context->request()->post('id');
            $unidade = $context->getUser()->getUnidade();
            if (!$servico_id || !$unidade) {
                return false;
            }
            $query = $this->em()->createQuery("UPDATE Novosga\Model\ServicoUnidade e SET e.status = :status WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('status', $status);
            $query->setParameter('servico', $servico_id);
            $query->setParameter('unidade', $unidade->getId());
            $response->success = $query->execute();
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
    public function habilita_servico(Context $context) {
        return $this->change_status($context, 1);
    }
    
    public function desabilita_servico(Context $context) {
        return $this->change_status($context, 0);
    }
    
    public function update_servico(Context $context) {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $id = (int) $context->request()->post('id');
            $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\ServicoUnidade e WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('servico', $id);
            $query->setParameter('unidade', $context->getUser()->getUnidade()->getId());
            $su = $query->getSingleResult();

            $sigla = $context->request()->post('sigla');
            $nome = $context->request()->post('nome');
            $local = $this->em()->find("Novosga\Model\Local", (int) $context->request()->post('local'));
            
            $su->setSigla($sigla);
            $su->setNome($nome);
            if ($local) {
                $su->setLocal($local);
            }
            $this->em()->merge($su);
            $this->em()->flush();
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
    public function reverte_nome(Context $context) {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $id = (int) $context->request()->post('id');
            $servico = $this->em()->find('Novosga\Model\Servico', $id);
            if (!$servico) {
                throw new Exception(_('Serviço inválido'));
            }
            $query = $this->em()->createQuery("UPDATE Novosga\Model\ServicoUnidade e SET e.nome = :nome WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('nome', $servico->getNome());
            $query->setParameter('servico', $servico->getId());
            $query->setParameter('unidade', $context->getUser()->getUnidade()->getId());
            $query->execute();
            $response->data['nome'] = $servico->getNome();
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
    public function acumular_atendimentos(Context $context) {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $unidade = $context->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade definida'));
            }
            $ab = new AtendimentoBusiness($this->em());
            $ab->acumularAtendimentos($unidade);
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
}
