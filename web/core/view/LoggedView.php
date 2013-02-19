<?php
namespace core\view;

use \core\SGA;
use \core\db\DB;
use \core\view\PageView;
use \core\SGAContext;
use \core\util\Arrays;
use \core\business\AcessoBusiness;

/**
 * LoggedView
 *
 * @author rogeriolino
 */
abstract class LoggedView extends PageView {
    
    const DIALOG_UNIDADE_ID = 'dialog-unidade';
    
    public function __construct($title) {
        parent::__construct($title);
    }
    
    public function header(SGAContext $context) {
        return parent::header($context) . $this->navbar($context);
    }
    
    public function navbar(SGAContext $context) {
        // montando o menu com os modulos disponiveis
        $navbar = '<div id="navbar">';
        $navbar .= '<h1 class="home">' . $this->builder->link(array('href' => './', 'label' => 'Início')) . '</h1>';
        $navbar .= '<ul id="navbar-menu">';
        // montando o menu dos modulos da unidade
        $usuario = $context->getUser();
        $unidade = $context->getUnidade();
        if ($unidade) {
            $modulos = AcessoBusiness::modulos($context->getUser(), \core\model\Modulo::MODULO_UNIDADE);
            $navbar .= '<li class="first">' . $this->builder->link(array('class' => 'module-menu', 'label' => $unidade->getNome()));
            $change = '<li class="separator">' . $this->builder->link(array('onclick' => $this->jsShowDialogUnidade(), 'label' => _('Trocar Unidade'))) . '</li>';
            $navbar .= $this->navbarModulosMenu($modulos, $change);
            $navbar .= '</li>';
        } else {
            $navbar .= '<li class="first">' . $this->builder->link(array('onclick' => $this->jsShowDialogUnidade(), 'label' => _('Escolher Unidade')));
        }
        $modulos = AcessoBusiness::modulos($context->getUser(), \core\model\Modulo::MODULO_GLOBAL);
        if (!empty($modulos)) {
            $navbar .= '<li>' . $this->builder->link(array('label' => _('Global'), 'class' => 'modules'));
            $navbar .= $this->navbarModulosMenu($modulos);
            $navbar .= '</li>';
        }
        $navbar .= '<li class="logout">' . $this->builder->link(array('href' => '?logout', 'label' => 'Sair')) . '</li>';
        $navbar .= '<li class="user">';
        $navbar .= $this->builder->link(array(
            'href' => '?home&page=perfil',
            'label' => sprintf(_('acessando como <strong>%s</strong>'), $usuario->getLogin()), 
            'class' => 'profile',
            'title' => _('Visualizar perfil')
        ));
        $navbar .= '</li>';
        $navbar .= '</ul>';
        $navbar .= '</div>';
        $navbar .= '<script type="text/javascript">SGA.Menu.init("#navbar-menu")</script>';
        return $navbar;
    }
    
    public function navbarModulosMenu($modulos, $extra = '') {
        $items = '<ul>';
        foreach ($modulos as $m) {
            $items .= '<li>' . $this->builder->link(array(
                    'href' => SGA::url(array(SGA::K_MODULE => $m->getChave())), 
                    'label' => $m->getNome())
                ) . '</li>';
        }
        return $items . $extra . '</ul>';
    }
    
    public function footer(SGAContext $context) {
        $html = '<div id="footer"><p>Novo SGA v' . SGA::VERSION . '</p></div>';
        $html .= '<spa id="ajax-loading" class="mini-loading" style="display:none"></span>';
        $html .= $this->changeUnidadeDialog($context);
        return $html . parent::footer($context);
    }
    
    private function changeUnidadeDialog(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $query = DB::getEntityManager()->createQuery("SELECT u FROM \core\model\Unidade u");
        $unidades = $query->getResult();
        $items = Arrays::toArray($unidades, array('nome'), 'id');
        // exibe a dialog para escolher a unidade se estiver na home
        $show = (!$unidade && !$context->getModulo());
        $style = (!$show) ? 'style="display:none"' : '';
        $content = '<div id="dialog-unidade" ' . $style . ' title="' . _('Unidade') . '"><div class="field">';
        $content .= '<label for="unidade" class="w175">' . _('Favor escolher a unidade') . '</label>';
        $content .= $this->builder->select(array(
            'id' => 'unidade',
            'items' => $items,
            'label' => _('Selecione'),
            'class' => 'w200',
            'default' => ($unidade ? $unidade->getId() : 0)
        ));
        $content .= '</div></div>';
        if ($show) {
            $content .= '<script type="text/javascript">' . $this->jsShowDialogUnidade() . '</script>';
        }
        return $content;
    }
    
    private function jsShowDialogUnidade() {
        $id = self::DIALOG_UNIDADE_ID;
        $label = _('Enviar');
        $url = "?home&" . SGA::K_PAGE . "=unidade";
        return "SGA.Unidades.show('$id', '$label', '$url')";
    }

}
