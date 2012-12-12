<?php
namespace core\view;

use \core\SGA;
use \core\view\ModuleView;
use \core\util\Strings;

/**
 * CrudView
 *
 * @author rogeriolino
 */
class CrudView extends ModuleView {

    public function searchBar() {
        $context = SGA::getContext();
        return '<div class="search">
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
                    )) .
                    $this->getBuilder()->button(array(
                        'type' => 'link',
                        'label' => _('Novo'),
                        'href' => $context->getModule()->link('edit'),
                        'class' => 'ui-button-primary btn-add'
                    ))
                .'
            </form>
        </div>
        ';
    }
    
    private function deleteForm() {
        return '<form id="form-delete" action="'. SGA::url('delete') .'" method="post"><input type="hidden" name="id" /></form>';
    }
    
    public function table(array $header, array $columns, $items, array $classes = array()) {
        $context = SGA::getContext();
        array_unshift($header, '#');
        array_push($header, '');
        array_unshift($columns, 'id');
        array_push($columns, $this->buttonEdit() . $this->buttonDelete());
        $classes[sizeof($columns) - 1] = 'btns';
        return $this->showMessages() . $this->getBuilder()->table(array(
            'header' => $header,
            'columns' => $columns,
            'classes' => $classes,
            'items' => $items
        )) . $this->deleteForm();
    }
    
    public function tree($title, $items) {
        $buttons = function($model) {
            if ($model->getLeft() > 1) {
                return $this->buttonEdit($model->getId()) . $this->buttonDelete($model->getId());
            }
            return '';
        };
        return $this->showMessages() . $this->getBuilder()->treeView(array(
            'title' => $title,
            'items' => $items,
            'buttons' => $buttons
        )) . $this->deleteForm();
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
            'href' => $context->getModule()->link('edit', array('id' => $id))
        ));
    }

    public function buttonDelete($id = null) {
        if (!$id) {
            $id = '{id}';
        }
        $context = SGA::getContext();
        return $this->getBuilder()->button(array(
            'id' => "btn-delete-$id",
            'type' => 'link',
            'class' => 'ui-button-error',
            'label' => _('Excluir'),
            'onclick' => "SGA.Form.confirm('" . _('Deseja realmente excluir?') . "', function() { $('#form-delete input').val(". $id ."); $('#form-delete').submit() })"
        ));
    }

}
