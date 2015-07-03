<?php

namespace Novosga\Model\Util;

use Novosga\Model\Usuario;
use Novosga\Model\Unidade;
use Novosga\Service\UsuarioService;
use Doctrine\ORM\EntityManager;
use Novosga\Service\ServicoService;

/**
 * Usuario utilizado para salvar na sessao. Assim evitar de salvar
 * as entidades do Doctrine.
 */
class UsuarioSessao
{
    // tipos de atendimentos
    const ATEND_TODOS = 1; // qualquer atendimento
    const ATEND_CONVENCIONAL = 2; // atendimento sem prioridade
    const ATEND_PRIORIDADE = 3; // atendimento prioritário

    private $id;
    private $unidade;
    private $unidadeId;
    private $local;
    private $ativo;
    private $lotacao;
    private $servicos;
    private $servicosIndisponiveis;
    private $permissoes;
    private $tipoAtendimento;
    private $sequenciaPrioridade;
    private $wrapped;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(Usuario $usuario)
    {
        $this->id = $usuario->getId();
        $this->ativo = true;
        $this->tipoAtendimento = self::ATEND_TODOS;
        $this->sequenciaPrioridade = 0;
        $this->wrapped = $usuario;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Retorna o número do local de atendimento (guiche, mesa, sala, etc) para atendimento na unidade atual.
     *
     * @return int
     */
    public function getLocal()
    {
        return $this->local;
    }

    public function setLocal($local)
    {
        $local = (int) $local;
        if ($local > 0) {
            $this->local = $local;
        } else {
            throw new Exception(_('Erro ao definir guiche do Usuário. Deve ser um número maior que zero.'));
        }
    }

    public function isAtivo()
    {
        return $this->ativo == true;
    }

    public function setAtivo($ativo)
    {
        $this->ativo = ($ativo == true);
    }

    /**
     * Retorna todas as permissoes do usuario.
     */
    public function getPermissoes()
    {
        if (!$this->permissoes) {
            $this->permissoes = array();
            $query = $this->em->createQuery("
                SELECT
                   p
                FROM
                    Novosga\Model\Lotacao l,
                    Novosga\Model\Permissao p
                WHERE
                    l.cargo = p.cargo AND
                    l.usuario = :usuario
            ");
            $query->setParameter('usuario', $this->getId());
            $permissoes = $query->getResult();
            foreach ($permissoes as $permissao) {
                $this->permissoes[] = new PermissaoSessao($this->getId(), $permissao);
            }
        }

        return $this->permissoes;
    }

    /**
     * Verifica se o usuaro tem permissao no modulo informado. Filtrando tambem
     * por cargo, caso seja informado.
     *
     * @param Novosga\Model\Modulo     $modulo
     * @param Novosga\Model\Util\Cargo $cargo
     *
     * @return bool
     */
    public function hasPermissao($modulo, $cargo = null)
    {
        $permissoes = $this->getPermissoes();
        // fazendo dois for para evitar de colocar outro if dentro do loop
        if ($cargo == null) {
            foreach ($permissoes as $permissao) {
                if ($modulo->getId() == $permissao->getModuloId()) {
                    return true;
                }
            }
        } else {
            foreach ($permissoes as $permissao) {
                if ($modulo->getId() == $permissao->getModuloId() && $cargo->getId() == $permissao->getCargoId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retorna a lotacao do usuario na unidade atual.
     *
     * @return Novosga\Model\Lotacao
     */
    public function getLotacao()
    {
        if (!$this->lotacao) {
            // pegando a lotacao do usuario na unidade escolhida
            $query = $this->em->createQuery("SELECT e FROM Novosga\Model\Lotacao e JOIN e.grupo g WHERE e.usuario = :usuario ORDER BY g.left DESC");
            $query->setParameter('usuario', $this->getId());
            $lotacoes = $query->getResult();
            foreach ($lotacoes as $lotacao) {
                // se o usuario esta ligado a alguma unidade
                if ($this->getUnidade()) {
                    // verifica se a lotacao eh do mesmo grupo ou um grupo pai do grupo da unidade
                    if ($lotacao->getGrupo()->getId() == $this->getUnidade()->getGrupo()->getId() || $this->getUnidade()->getGrupo()->isChild($lotacao->getGrupo())) {
                        $this->lotacao = $lotacao;
                        break;
                    }
                } else {
                    $this->lotacao = $lotacao;
                    break;
                }
            }
        }

        return $this->lotacao;
    }

    /**
     * Retorna os servicos do usuario na unidade atual.
     *
     * @return \Novosga\Model\ServicoUsuario[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getServicos()
    {
        if (!$this->servicos && $this->getUnidade()) {
            $service = new ServicoService($this->em);
            $this->servicos = $service->servicosUsuario($this->getUnidade(), $this->getId());
        }

        return $this->servicos;
    }

    public function setServicos($servicos)
    {
        $this->servicos = $servicos;
    }

    /**
     * Retorna os servicos que o usuario nao atende na unidade atual.
     *
     * @return Locatacao
     */
    public function getServicosIndisponiveis()
    {
        if (!$this->servicosIndisponiveis && $this->getUnidade()) {
            $service = new ServicoService($this->em);
            $this->servicosIndisponiveis = $service->servicosIndisponiveis($this->getUnidade(), $this->getId());
        }

        return $this->servicosIndisponiveis;
    }

    /**
     * @return Novosga\Model\Unidade
     */
    public function getUnidade()
    {
        if (!$this->unidade) {
            if (!$this->unidadeId && $this->em) {
                $meta = (new UsuarioService($this->em))->meta($this->wrapped, UsuarioService::ATTR_UNIDADE);
                $this->unidadeId = $meta ? (int) $meta->getValue() : null;
            }
            if ($this->unidadeId > 0) {
                $this->unidade = $this->em->find("Novosga\Model\Unidade", $this->unidadeId);
            }
        }

        return $this->unidade;
    }

    public function setUnidade(Unidade $unidade)
    {
        $this->unidade = $unidade;
        $this->unidadeId = $unidade->getId();
        if ($this->em) {
            (new UsuarioService($this->em))->meta($this->wrapped, UsuarioService::ATTR_UNIDADE, $unidade->getId());
        }
    }

    public function getTipoAtendimento()
    {
        return $this->tipoAtendimento;
    }

    public function setTipoAtendimento($tipoAtendimento)
    {
        $this->tipoAtendimento = $tipoAtendimento;
    }

    public function getSequenciaPrioridade()
    {
        return $this->sequenciaPrioridade;
    }

    public function setSequenciaPrioridade($sequenciaPrioridade)
    {
        $this->sequenciaPrioridade = $sequenciaPrioridade;
    }

    public function getLogin()
    {
        return $this->getWrapped()->getLogin();
    }

    public function getNome()
    {
        return $this->getWrapped()->getNome();
    }

    public function getSobrenome()
    {
        return $this->getWrapped()->getSobrenome();
    }

    public function getSenha()
    {
        return $this->getWrapped()->getSenha();
    }

    /**
     * @return Novosga\Model\Usuario
     */
    public function getWrapped()
    {
        if (!$this->wrapped) {
            $this->wrapped = $this->em->find("Novosga\Model\Usuario", $this->id);
        }

        return $this->wrapped;
    }

    public function getEm()
    {
        return $this->em;
    }

    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

    public function __sleep()
    {
        return array('id', 'unidadeId', 'ativo', 'local', 'tipoAtendimento', 'permissoes', 'sequenciaPrioridade');
    }

    /**
     * Métodos desconhecidos serão chamados no modelo usuário.
     *
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments)
    {
        $method = new \ReflectionMethod($this->getWrapped(), $name);

        return $method->invokeArgs($this->getWrapped(), $arguments);
    }

    public function __toString()
    {
        return $this->getNome();
    }
}
