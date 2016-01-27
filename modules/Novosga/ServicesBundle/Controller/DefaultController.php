<?php

namespace Novosga\ServicesBundle\Controller;

use Novosga\Context;
use Novosga\Entity\SequencialModel;
use Novosga\Entity\Servico;
use Mangati\BaseBundle\Controller\CrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * ServicosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultController extends CrudController
{
    
    public function __construct() {
        parent::__construct(Servico::class);
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/", name="novosga_services_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('NovosgaServicesBundle:Default:index.html.twig');
    }
    
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/search.json", name="novosga_services_search")
     */
    public function searchAction(Request $request) 
    {
        $query = $this
                ->getDoctrine()
                ->getManager()
                ->createQueryBuilder()
                ->select('e')
                ->from(Servico::class, 'e')
                ->getQuery();
        
        return $this->dataTable($request, $query, false);
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/edit/{id}", name="novosga_services_edit")
     */
    public function editAction(Request $request, $id = 0)
    {
        return $this->edit('NovosgaServicesBundle:Default:edit.html.twig', $request, $id);
    }

    protected function preSave(Context $context, SequencialModel $model)
    {
        $id_macro = (int) $context->request()->post('id_macro');
        $macro = $this->em()->find("Novosga\Entity\Servico", $id_macro);
        $model->setMestre($macro);
    }

    protected function postSave(Context $context, SequencialModel $model)
    {
        // um subserviço não pode aparecer na lista de serviços da unidade (triagem). issue #257
        if ($model->getId() && $model->getMestre()) {
            $query = $this->em()->createQuery("DELETE FROM Novosga\Entity\ServicoUsuario e WHERE e.servico = :servico");
            $query->setParameter('servico', $model->getId());
            $query->execute();
            $query = $this->em()->createQuery("DELETE FROM Novosga\Entity\ServicoUnidade e WHERE e.servico = :servico");
            $query->setParameter('servico', $model->getId());
            $query->execute();
        }
    }

    /**
     * Verifica se já existe unidade usando o serviço.
     *
     * @param Novosga\Entity\SequencialModel $model
     */
    protected function preDelete(Context $context, SequencialModel $model)
    {
        $error = _('Já existem atendimentos para o serviço que está tentando remover');
        // quantidade de atendimentos do servico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Entity\Atendimento e JOIN e.servicoUnidade su WHERE su.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // quantidade de atendimentos do servico, no historico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Entity\ViewAtendimento e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // apagando vinculo com as unidades
        $this->em()->beginTransaction();
        $query = $this->em()->createQuery("DELETE FROM Novosga\Entity\ServicoUsuario e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $query->execute();
        $query = $this->em()->createQuery("DELETE FROM Novosga\Entity\ServicoUnidade e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $query->execute();
    }

    protected function postDelete(Context $context, SequencialModel $model)
    {
        $this->em()->commit();
    }

    public function subservicos(Context $context)
    {
        $response = new \Novosga\Http\JsonResponse();
        $id = $context->request()->get('id');
        $servico = $this->findById($id);
        if ($servico) {
            foreach ($servico->getSubServicos() as $sub) {
                $response->data[] = [
                    'id'   => $sub->getId(),
                    'nome' => $sub->getNome(),
                ];
            }
            $response->success = true;
        }
        echo $response->toJson();
        exit();
    }
    
    protected function createFormType() 
    {
        return \AppBundle\Form\ServicoType::class;
    }
}
