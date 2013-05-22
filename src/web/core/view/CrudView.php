<?php
namespace core\view;

use \core\SGA;
use \core\SGAContext;
use \core\view\ModuleView;
use \core\util\Arrays;
use \core\util\Strings;

/**
 * CrudView
 *
 * @author rogeriolino
 */
class CrudView extends ModuleView {
    
    protected $canAdd;
    protected $canEdit;
    protected $canDelete;
    
    public function __construct($title, $subtitle = '', $canAdd = true, $canEdit = true, $canDelete) {
        parent::__construct($title, $subtitle);
        $this->canAdd = $canAdd; 
        $this->canEdit = $canEdit; 
        $this->canDelete = $canDelete;
    }

    public function searchBar() {
        $context = SGA::getContext();
        $html = '<div class="search">
            <form method="post" action="'. SGA::url() . '">
                <span>
                    <input id="search-box" type="text" name="s" value="'. Strings::doubleQuoteSlash($context->getRequest()->getParameter('s')) .'" placeholder="'. _('buscar') .'" />
                    <script type="text/javascript">SGA.Form.searchBox("search-box")</script>
                </span>
                ' .
                    $this->getBuilder()->button(array(
                        'type' => 'submit',
                        'label' => _('Buscar'),
                        'icon' => 'ui-icon-search'
                    ));
        if ($this->canAdd) {
            $html .= $this->getBuilder()->button(array(
                'type' => 'link',
                'label' => _('Novo'),
                'href' => $context->getModulo()->link('edit'),
                'class' => 'ui-button-primary btn-add'
            ));
        }
        $html .= '
            </form>
        </div>
        ';
        return $html;
    }
    
    private function deleteForm() {
        return '<form id="form-delete" action="'. SGA::url('delete') .'" method="post"><input type="hidden" name="id" /></form>';
    }
    
    public function table(array $header, array $columns, $items, array $classes = array()) {
        array_unshift($header, '#');
        array_push($header, '');
        array_unshift($columns, 'id');
        $buttons = '';
        if ($this->canEdit) {
            $buttons .= $this->buttonEdit();
        }
        if ($this->canDelete) {
            $buttons .= $this->buttonDelete();
        }
        if (!empty($buttons)) {
            array_push($columns, $buttons);
            $classes[sizeof($columns) - 1] = 'btns';
        }
        return $this->getBuilder()->table(array(
            'id' => 'table-list',
            'header' => $header,
            'columns' => $columns,
            'classes' => $classes,
            'items' => $items
        ));
    }
    
    public function tree($title, $items) {
        $view = $this;
        $buttons = function($model) use ($view) {
            $btns = $view->buttonEdit($model->getId());
            if ($model->getLeft() > 1) {
                $btns .= $view->buttonDelete($model->getId());
            }
            return $btns;
        };
        return $this->showMessages() . $this->getBuilder()->treeView(array(
            'title' => $title,
            'items' => $items,
            'buttons' => $buttons
        ));
    }
    
    public function statusLabel($status) {
        if ($status == 1) { 
            $class = 'active';
            $label = _('Ativo');
        } else {
            $class = 'inactive';
            $label = _('Inativo');
        }
        return '<span class="' . $class . '">' . $label . '</span>';
    }

    public function buttonEdit($id = null) {
        if (!$id) {
            $id = '{id}';
        }
        $context = SGA::getContext();
        return $this->getBuilder()->button(array(
            'id' => "btn-edit-$id",
            'type' => 'link',
            'label' => _('Editar'),
            'href' => $context->getModulo()->link('edit', array('id' => $id))
        ));
    }

    public function buttonDelete($id = null) {
        if (!$id) {
            $id = '{id}';
        }
        return $this->getBuilder()->button(array(
            'id' => "btn-delete-$id",
            'type' => 'link',
            'class' => 'ui-button-error',
            'label' => _('Excluir'),
            'onclick' => "SGA.Form.confirm('" . _('Deseja realmente excluir?') . "', function() { $('#form-delete input').val(". $id ."); $('#form-delete').submit() })"
        ));
    }

    public function editButtonsBar() {
        $html = '<div class="buttons">';
        $html .= '<span class="btns">';
        $html .= $this->getBuilder()->button(array(
            'type' => 'submit',
            'label' => _('Salvar'),
            'class' => 'ui-button-primary btn-save'
        ));
        $html .= $this->getBuilder()->button(array(
            'type' => 'link',
            'label' => _('Voltar'),
            'href' => SGA::url('index'),
            'class' => 'btn-back'
        ));
        $id = (int) Arrays::value($_GET, 'id');
        if ($id > 0) {
            $html .= $this->buttonDelete($id);
        }
        $html .= '</span>';
        $html .= '<p class="required-desc">' . _('Campos obrigatórios') . '</p>';
        $html .= '</div>';
        return $html;
    }
    
    public function footer(SGAContext $context) {
        return $this->deleteForm() . parent::footer($context);
    }

}
