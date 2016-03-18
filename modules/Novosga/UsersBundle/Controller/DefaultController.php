<?php

namespace Novosga\UsersBundle\Controller;

use Exception;
use Novosga\Entity\Lotacao;
use Novosga\Entity\Usuario;
use AppBundle\Form\UsuarioType;
use Mangati\BaseBundle\Controller\CrudController;
use Mangati\BaseBundle\Event\CrudEvent;
use Mangati\BaseBundle\Event\CrudEvents;
use Novosga\Http\JsonResponse;
use Novosga\Service\ServicoService;
use Novosga\Util\Arrays;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * UsuariosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultController extends CrudController
{
    
    public function __construct()
    {
        parent::__construct(Usuario::class);
    }
   
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/", name="novosga_users_index")
     */
    public function indexAction(Request $request) 
    {
        $unidade = $request->getSession()->get('unidade');
        
        if (!$unidade) {
            return $this->redirectToRoute('home');
        }
        
        return $this->render('NovosgaUsersBundle:default:index.html.twig');
    }
   
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/search.json", name="novosga_users_search")
     */
    public function searchAction(Request $request) 
    {
        $usuario = $this->getUser();
        $unidade = $request->getSession()->get('unidade');
        
        if (!$usuario || !$unidade) {
            return $this->redirectToRoute('home');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em
                ->createQueryBuilder()
                ->select('e')
                ->from(Usuario::class, 'e')
                ->join('e.lotacoes', 'l')
                ->where('l.grupo = :grupo')
                ->setParameters([
                    'grupo' => $unidade->getGrupo()
                ])
                ->getQuery();
        
        return $this->dataTable($request, $query, false);
    }
    
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/edit/{id}")
     */
    public function editAction(Request $request, $id = 0)
    {
        $em = $this->getDoctrine()->getManager();
        
        $this
            ->addEventListener(CrudEvents::FORM_RENDER, function (CrudEvent $event) use ($em) {
                $params = $event->getData();
                $entity = $params['entity'];

                // lotacoes do usuario
                $rs = $em->getRepository(\Novosga\Entity\Lotacao::class)
                        ->getLotacoes($entity);
                $lotacoes = [];
                foreach ($rs as $lotacao) {
                    $lotacoes[] = [
                        'grupo_id' => $lotacao->getGrupo()->getId(),
                        'grupo'    => $lotacao->getGrupo()->getNome(),
                        'cargo_id' => $lotacao->getCargo()->getId(),
                        'cargo'    => $lotacao->getCargo()->getNome(),
                    ];
                }
                // servicos do usuario
                $rs = $em->getRepository(\Novosga\Entity\ServicoUsuario::class)
                        ->findByUsuario($entity);
                $servicos = [];
                foreach ($rs as $servico) {
                    $servicos[] = [
                        'unidade_id' => $servico->getUnidade()->getId(),
                        'unidade'    => $servico->getUnidade()->getNome(),
                        'servico_id' => $servico->getServico()->getId(),
                        'servico'    => $servico->getServico()->getNome(),
                    ];
                }

                // unidades
                $unidades = $em->getRepository(\Novosga\Entity\Unidade::class)->findAll();

                // cargos disponiveis
                $cargos = $em->getRepository(\Novosga\Entity\Cargo::class)->findAll();

                $params['unidades'] = $unidades;
                $params['cargos']   = $cargos;
                $params['lotacoes'] = $lotacoes;
                $params['servicos'] = $servicos;
            });
        
        return $this->edit('NovosgaUsersBundle:default:edit.html.twig', $request, $id);
    }

    /**
     * Retorna os grupos disponíveis para serem atribuidos ao usuário. Descartando os grupos com ids informados no parâmetro exceto.
     *
     * @param array $exceto
     */
    private function grupos_disponiveis(array $exceto)
    {
        // grupos disponiveis (grupos que o usuario nao esta vinculados e que nao sao filhos e nem pai do que esta)
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                Novosga\Entity\Grupo e
            WHERE
                NOT EXISTS (
                    SELECT
                        g2.id
                    FROM
                        Novosga\Entity\Grupo g2
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
     *
     * @param Novosga\Context $context
     */
    public function grupos(Context $context)
    {
        $exceto = $request->get('exceto');
        $exceto = Arrays::valuesToInt(explode(',', $exceto));
        $response = new JsonResponse(true);
        $grupos = $this->grupos_disponiveis($exceto);
        foreach ($grupos as $g) {
            $response->data[] = ['id' => $g->getId(), 'nome' => $g->getNome()];
        }

        return $response;
    }

    /**
     * Retorna as permissões do cargo informado.
     *
     * @param Novosga\Context $context
     */
    public function permissoes_cargo(Context $context)
    {
        $response = new JsonResponse(true);
        $id = (int) $request->get('cargo');
        $query = $this->em()->createQuery("SELECT m.nome FROM Novosga\Entity\Permissao e JOIN e.modulo m WHERE e.cargo = :cargo ORDER BY m.nome");
        $query->setParameter('cargo', $id);
        $response->data = $query->getResult();

        return $response;
    }

    /**
     * Retorna os serviços habilitados na unidade informada. Descartando os serviços com ids informados no parâmetro exceto.
     *
     * @param Novosga\Context $context
     */
    public function servicos_unidade(Context $context)
    {
        $response = new JsonResponse(true);
        $id = (int) $request->get('unidade');

        $exceto = $request->get('exceto');
        $exceto = Arrays::valuesToInt(explode(',', $exceto));
        $exceto = implode(',', $exceto);

        $service = new ServicoService($this->em());
        $response->data = $service->servicosUnidade($id, "e.status = 1 AND s.id NOT IN ($exceto)");

        return $response;
    }

    /**
     * Altera a senha do usuario que está sendo editado.
     *
     * @param Novosga\Context $context
     */
    public function alterar_senha(Context $context)
    {
        $response = new JsonResponse();
        $id = (int) $request->get('id');
        $senha = $request->get('senha');
        $confirmacao = $request->get('confirmacao');
        $usuario = $this->findById($id);
        if ($usuario) {
            try {
                $hash = $this->app()->getAcessoService()->verificaSenha($senha, $confirmacao);
                $query = $this->em()->createQuery("UPDATE Novosga\Entity\Usuario u SET u.senha = :senha WHERE u.id = :id");
                $query->setParameter('senha', $hash);
                $query->setParameter('id', $usuario->getId());
                $query->execute();
                $response->success = true;
            } catch (Exception $e) {
                $response->message = $e->getMessage();
            }
        } else {
            $response->message = _('Usuário inválido');
        }

        return $response;
    }
    
    protected function editFormOptions(Request $request, $entity)
    {
        $options = parent::editFormOptions($request, $entity);
        $options['entity'] = $entity;
        return $options;
    }

    protected function createFormType()
    {
        return UsuarioType::class;
    }

}
