<?php

namespace modules\sga\usuarios;

use Exception;
use Novosga\Context;
use Novosga\Util\Arrays;
use Novosga\Http\JsonResponse;
use Novosga\Model\SequencialModel;
use Novosga\Model\Usuario;
use Novosga\Controller\CrudController;

/**
 * UsuariosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UsuariosController extends CrudController
{
    protected function createModel()
    {
        return new Usuario();
    }

    protected function requiredFields()
    {
        return array('login', 'nome', 'sobrenome');
    }

    public function edit(Context $context, $id = 0)
    {
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
                'cargo' => $lotacao->getCargo()->getNome(),
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
                'servico' => $servico->getServico()->getNome(),
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

    protected function preSave(Context $context, SequencialModel $model)
    {
        $login = $context->request()->post('login');
        if (!preg_match('/^[a-zA-Z0-9\.]+$/', $login)) {
            throw new Exception(_('O login deve conter somente letras e números.'));
        }
        if (strlen($login) < 5 || strlen($login) > 20) {
            throw new Exception(_('O login deve possuir entre 5 e 20 caracteres (letras ou números).'));
        }
        $lotacoes = $context->request()->post('lotacoes', array());
        if (empty($lotacoes)) {
            throw new Exception(_('O usuário deve possuir pelo menos uma lotação.'));
        }
        if ($model->getId() == 0) {
            // para novos usuarios, tem que informar a senha
            $senha = $context->request()->post('senha');
            $confirmacao = $context->request()->post('senha2');
            // verifica e codifica a senha
            $model->setSenha($this->app()->getAcessoService()->verificaSenha($senha, $confirmacao));
            $model->setStatus(1);
            $model->setSessionId('');
        } else {
            $model->setStatus((int) $context->request()->post('status'));
        }
        // verificando novo login ou alteracao
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Model\Usuario e WHERE e.login = :login AND e.id != :id");
        $query->setParameter('login', $model->getLogin());
        $query->setParameter('id', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total']) {
            throw new \Exception(_('O login informado já está cadastrado para outro usuário.'));
        }
    }

    protected function postSave(Context $context, SequencialModel $model)
    {
        // lotacoes - atualizando permissoes do cargo
        $this->em()
                ->createQuery("DELETE FROM Novosga\Model\Lotacao e WHERE e.usuario = :usuario")
                ->setParameter('usuario', $model->getId())
                ->execute()
        ;
        $lotacoes = $context->request()->post('lotacoes', array());
        if (!empty($lotacoes)) {
            foreach ($lotacoes as $item) {
                $value = explode(',', $item);
                $lotacao = new \Novosga\Model\Lotacao();
                $lotacao->setGrupo($this->em()->find('Novosga\Model\Grupo', $value[0]));
                $lotacao->setCargo($this->em()->find('Novosga\Model\Cargo', $value[1]));
                $lotacao->setUsuario($model);
                $this->em()->persist($lotacao);
            }
            $this->em()->flush();
        }
        // servicos
        $this->em()
                ->createQuery("DELETE FROM Novosga\Model\ServicoUsuario e WHERE e.usuario = :usuario")
                ->setParameter('usuario', $model->getId())
                ->execute()
        ;
        $servicos = $context->request()->post('servicos', array());
        if (!empty($servicos)) {
            foreach ($servicos as $servico) {
                $value = explode(',', $servico);
                $su = new \Novosga\Model\ServicoUsuario();
                $su->setUnidade($this->em()->find('Novosga\Model\Unidade', $value[0]));
                $su->setServico($this->em()->find('Novosga\Model\Servico', $value[1]));
                $su->setUsuario($model);
                $this->em()->persist($su);
            }
            $this->em()->flush();
        }
    }

    protected function preDelete(Context $context, SequencialModel $model)
    {
        if ($context->getUser()->getId() === $model->getId()) {
            throw new \Exception(_('Não é possível excluir si próprio.'));
        }
        // verificando a quantidade de atendimentos do usuario
        $total = 0;
        $models = array('Atendimento', 'ViewAtendimento');
        foreach ($models as $atendimentoModel) {
            $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Model\\$atendimentoModel e WHERE e.usuario = :usuario");
            $query->setParameter('usuario', $model->getId());
            $rs = $query->getSingleResult();
            $total += $rs['total'];
        }
        if ($total > 0) {
            throw new \Exception(_('Não é possível excluir esse usuário pois o mesmo já realizou atendimentos.'));
        }
        // excluindo vinculos do usuario (servicos e lotacoes)
        $models = array('ServicoUsuario', 'Lotacao');
        foreach ($models as $vinculoModel) {
            $query = $this->em()->createQuery("DELETE FROM Novosga\Model\\$vinculoModel e WHERE e.usuario = :usuario");
            $query->setParameter('usuario', $model->getId());
            $query->execute();
        }
    }

    protected function search($arg)
    {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Usuario e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.login) LIKE :arg");
        $query->setParameter('arg', $arg);

        return $query;
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
     *
     * @param Novosga\Context $context
     */
    public function grupos(Context $context)
    {
        $exceto = $context->request()->get('exceto');
        $exceto = Arrays::valuesToInt(explode(',', $exceto));
        $response = new JsonResponse(true);
        $grupos = $this->grupos_disponiveis($exceto);
        foreach ($grupos as $g) {
            $response->data[] = array('id' => $g->getId(), 'nome' => $g->getNome());
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
        $id = (int) $context->request()->get('cargo');
        $query = $this->em()->createQuery("SELECT m.nome FROM Novosga\Model\Permissao e JOIN e.modulo m WHERE e.cargo = :cargo ORDER BY m.nome");
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
        $id = (int) $context->request()->get('unidade');

        $exceto = $context->request()->get('exceto');
        $exceto = Arrays::valuesToInt(explode(',', $exceto));
        $exceto = implode(',', $exceto);

        $service = new \Novosga\Service\ServicoService($this->em());
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
        $id = (int) $context->request()->post('id');
        $senha = $context->request()->post('senha');
        $confirmacao = $context->request()->post('confirmacao');
        $usuario = $this->findById($id);
        if ($usuario) {
            try {
                $hash = $this->app()->getAcessoService()->verificaSenha($senha, $confirmacao);
                $query = $this->em()->createQuery("UPDATE Novosga\Model\Usuario u SET u.senha = :senha WHERE u.id = :id");
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
}
