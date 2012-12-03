<?php
namespace core\view;

use \core\SGA;
use \core\db\DB;
use \core\view\PageView;
use \core\SGAContext;
use \core\util\Arrays;

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
        // montando o menu
        $navbar = '<div id="navbar" class="ui-helper-reset ui-state-default ui-corner-all">';
        $navbar .= '<h1 class="home">' . $this->builder->link(array('href' => './', 'label' => 'Início')) . '</h1>';
        $navbar .= '<ul id="navbar-menu">';
        // montando o menu dos modulos da unidade
        $unidade = $context->getUser()->getUnidade();
        $queryModulos = DB::getEntityManager()->createQuery("SELECT m FROM \core\model\Modulo m WHERE m.status = 1 AND m.tipo = :tipo");
        if ($unidade) {
            $queryModulos->setParameter('tipo', \core\model\Modulo::MODULO_UNIDADE);
            $modulos = $queryModulos->getResult();
            $navbar .= '<li class="first">' . $this->builder->link(array('class' => 'module-menu', 'label' => $unidade->getNome()));
            $change = '<li class="separator">' . $this->builder->link(array('onclick' => $this->jsShowDialogUnidade(), 'label' => _('Trocar Unidade'))) . '</li>';
            $navbar .= $this->navbarModulosMenu($modulos, $change);
            $navbar .= '</li>';
        } else {
            $navbar .= '<li class="first">' . $this->builder->link(array('onclick' => $this->jsShowDialogUnidade(), 'label' => _('Escolher Unidade')));
        }
        $navbar .= '<li>' . $this->builder->link(array('label' => _('Global'), 'class' => 'modules'));
        // menu com os modulos disponiveis
        $queryModulos->setParameter('tipo', \core\model\Modulo::MODULO_GLOBAL);
        $modulos = $queryModulos->getResult();
        $navbar .= $this->navbarModulosMenu($modulos);
        $navbar .= '</li>';
        $navbar .= '<li class="logout">' . $this->builder->link(array('href' => '?logout', 'label' => 'Sair')) . '</li>';
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
    
    public function content(SGAContext $context) {
        $content = parent::content($context);
        if ($context->getResponse()->renderView()) {
            $content .= '<div id="sga-clock" title="' . _('Data e hora no servidor') . '"></div>';
        }
        return $content;
    }
    
    public function footer(SGAContext $context) {
        $clock = '<script type="text/javascript">SGA.Clock.init("sga-clock", ' . (time() * 1000) . ');</script>';
        $html = $this->changeUnidadeDialog($context) . $clock;
        return $html . parent::footer($context);
    }
    
    private function changeUnidadeDialog(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $query = DB::getEntityManager()->createQuery("SELECT u FROM \core\model\Unidade u");
        $unidades = $query->getResult();
        $items = Arrays::toArray($unidades, array('nome'), 'id');
        // exibe a dialog para escolher a unidade se estiver na home
        $show = (!$unidade && !$context->getModule());
        $style = (!$show) ? 'style="display:none"' : '';
        $content = '<div id="dialog-unidade" ' . $style . ' title="' . _('Unidade') . '"><div class="field">';
        $content .= '<label for="unidade" class="w175">' . _('Favor escolher a unidade') . '</label>';
        $content .= $this->builder->select(array(
            'id' => 'unidade',
            'items' => $items,
            'label' => _('Selectione'),
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
