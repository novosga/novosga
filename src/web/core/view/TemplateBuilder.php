<?php
namespace core\view;

use \Exception;
use \core\SGA;
use \core\util\Arrays;
use \core\util\Objects;
use \core\util\Strings;

/**
 * TemplateBuilder
 *
 * @author rogeriolino
 */
class TemplateBuilder {
    
    private static $ids = 0;
    
    private function id($prefix) {
        $prefix = empty($prefix) ? 'uic' : $prefix;
        return "$prefix-" . ++self::$ids;
    }
    
    public function tag($name, $attrs = null, $inner = null) {
        if (!is_array($attrs)) {
            $inner = $attrs;
            $attrs = array();
        }
        $tag = '<' . $name . ' ';
        foreach ($attrs as $attrName => $attrValue) {
            $tag .= $attrName . '="' . Strings::doubleQuoteSlash($attrValue) . '" ';
        }
        if ($inner === null) { // simple tag
            $tag .= '/>';
        } else {
            $tag .= '>' . $inner . '</'. $name .'>';
        }
        return $tag;
    }
    
    public function state($type, $arg) {
        if (!is_array($arg)) {
            $arg = array('label' => $arg);
        }
        $arg['class'] = "ui-state-$type ui-corner-all";
        $label = Arrays::value($arg, 'label');
        Arrays::removeKey($arg, 'label');
        $html = Arrays::value($arg, 'html');
        Arrays::removeKey($arg, 'html');
        $html = '<p>' . $label . '</p>';
        return $this->tag('div', array('class' => 'ui-widget'), $this->tag('div', $arg, $html));
    }
    
    public function icon($arg) {
        if (!is_array($arg)) {
            $arg = array('class' => $arg);
        }
        $arg['class'] = 'ui-icon ' . Arrays::value($arg, 'class');
        return $this->tag('span', $arg, '');
    }
    
    public function highlight($arg) {
        return $this->state('highlight', $arg);
    }
    
    public function defaultState($arg) {
        return $this->state('default', $arg);
    }
    
    public function error($arg) {
        return $this->state('error', $arg);
    }
    
    public function success($arg) {
        return $this->state('success', $arg);
    }
    
    public function checkbox($arg) {
        if (!is_array($arg)) {
            $arg = array();
        }
        $checked = Arrays::value($arg, 'checked', false);
        if ($checked) {
            $arg['checked'] = "checked";
        } else {
            Arrays::removeKey($arg, 'checked');
        }
        return $this->input('checkbox', $arg);
    }
    
    public function input($type, $arg) {
        if (!is_array($arg)) {
            $arg = array();
        }
        $arg['type'] = $type;
        return $this->tag('input', $arg);
    }
    
    public function button($arg) {
        if (!is_array($arg)) {
            $arg = array();
        }
        $tag = 'button';
        $type = Arrays::value($arg, 'type');
        if (!empty($type)) {
            if ($type == 'link') {
                $tag = 'a';
            } else {
                $tag = 'input';
            }
        }
        $arg['id'] = Arrays::value($arg, 'id', $this->id('button'));
        $label = Arrays::value($arg, 'label');
        Arrays::removeKey($arg, 'label');
        
        $inner = '';
        if (!empty($label)) {
            if ($tag == 'input') {
                $arg['value'] = $label;
            } else {
                $inner = $label;
            }
        }
        
        $props = array();
        if (empty($label)) {
            $props['text'] = false;
        }
        $icon = Arrays::value($arg, 'icon');
        Arrays::removeKey($arg, 'icon');
        if (!empty($icon)) {
            $props['icons'] = array('primary' => $icon);
        }
        return $this->tag($tag, $arg, $inner) . '<script type="text/javascript">$("#'. $arg['id'] .'").button('. json_encode($props) .')</script>';
    }
    
    public function listView($items) {
        $html = '<ul>';
        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                $html .= $this->listItem($item);
            }
        }
        $html .= '</ul>';
        return $html;
    }
    
    public function listItem($item) {
        if (!is_array($item)) {
            $item = array();
        }
        if (isset($item['href']) || isset($item['onclick'])) {
            $item = $this->link($item);
        }
        return "<li>$item</li>";
    }
    
    public function link($arg) {
        if (!is_array($arg)) {
            $arg = array('label' => $arg);
        }
        $arg['id'] = Arrays::value($arg, 'id', $this->id('link'));
        $arg['title'] = Arrays::value($arg, 'title');
        $arg['class'] = Arrays::value($arg, 'class');
        $arg['href'] = Arrays::value($arg, 'href');
        $arg['onclick'] = Arrays::value($arg, 'onclick');
        $label = Arrays::value($arg, 'label');
        Arrays::removeKey($arg, 'label');
        if (empty($arg['href'])) {
            $arg['href'] = 'javascript:void(0)';
        }
        if (empty($arg['onclick'])) {
            Arrays::removeKey($arg, 'onclick');
        }
        return $this->tag('a', $arg, '<span>'. $label .'</span>');
    }
    
    public function select($arg) {
        if (!is_array($arg)) {
            $arg = array();
        }
        $select = $arg;
        Arrays::removeKeys($select, array('items', 'label', 'default'));
        return $this->tag('select', $select, $this->items($arg));
    }
    
    private function items($arg) {
        $content = '';
        $items = Arrays::value($arg, 'items', array());
        $label = Arrays::value($arg, 'label');
        $default = Arrays::value($arg, 'default', '');
        if ($default instanceof \core\model\SequencialModel) {
            $default = $default->getId();
        }
        if (!empty($label)) {
            $content .= '<option value="">' . $label . '</option>';
        }
        foreach ($items as $k => $v) {
            if (is_array($v)) { // option group
                $content .= '<optgroup>' . $k . '</optgroup>';
                $content .= $this->items($v);
            } else {
                if ($v instanceof \core\model\SequencialModel) {
                    $k = $v->getId();
                }
                $selected = ("$default" === "$k") ? ' selected="selected"' : '';
                $content .= '<option value="' . Strings::doubleQuoteSlash($k) . '"' . $selected . '>' . $v . '</option>';
            }
        }
        return $content;
    }
    
    public function dialog($arg) {
        if (!is_array($arg)) {
            $arg = array();
        }
        $id = Arrays::value($arg, 'id', $this->id('dialog'));
        $title = Arrays::value($arg, 'title');
        $content = Arrays::value($arg, 'content');
        $closeble = Arrays::value($arg, 'closeble', true);
        $modal = Arrays::value($arg, 'modal', false);
        if ($closeble) {
            $close = $this->link(array(
                'href' => 'javascript:void(0)',
                'class' => 'ui-dialog-titlebar-close ui-corner-all',
                'label' => $this->tag('span', array(
                    'class' => 'ui-icon ui-icon-closethick'
                ), 'close')
            ));
        } else {
            $close = '';
        }
        $html = '';
        if ($modal) {
            $html .= '<div class="ui-widget-overlay" style="width:100%;height:100%;position:fixed"></div>';
        }
        $html .= '
        <div id="'. $id .'" class="ui-dialog ui-widget ui-widget-content ui-corner-all">
            <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
                <span class="ui-dialog-title">' . $title . '</span> '. $close . '
            </div>
            <div class="ui-dialog-content ui-widget-content">
                ' . $content . '
            </div>';
        $buttons = Arrays::value($arg, 'buttons');
        if (!empty($buttons)) {
            $html .= '
                <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
                    <div class="ui-dialog-buttonset">
                        ' . $buttons . '
                    </div>
                </div>';
        }
        $html .= '</div><script type="text/javascript">';
        $draggable = Arrays::value($arg, 'draggable', true);
        if ($draggable) {
            $html .= '$("#'. $id .'").draggable({handle: "div.ui-dialog-titlebar", create: function() { $(this).center() }});';
        } else {
            $html .= '$("#'. $id .'").center();';
        }
        if ($closeble) {
            $fn = '$("#'. $id .'").hide();';
            if ($modal) {
                $fn .= '$(".ui-widget-overlay").remove();';
            }
            $html .= '$("#'. $id .' .ui-icon-closethick").on("click", function() { ' . $fn . '});';
        }
        $html .= '</script>';
        return $html;
    }
    
    public function table($arg) {
        if (!is_array($arg)) {
            $arg = array();
        }
        $arg['id'] = Arrays::value($arg, 'id', $this->id('table'));
        $header = Arrays::value($arg, 'header', array());
        Arrays::removeKey($arg, 'header');
        $footer = Arrays::value($arg, 'footer', array());
        Arrays::removeKey($arg, 'footer');
        $items = Arrays::value($arg, 'items', array());
        Arrays::removeKey($arg, 'items');
        $columns = Arrays::value($arg, 'columns', array());
        Arrays::removeKey($arg, 'columns');
        $classes = Arrays::value($arg, 'classes', array());
        Arrays::removeKey($arg, 'classes');
        $arg['class'] = Arrays::value($arg, 'class') . ' ui-data-table';
        $table = '';
        if (sizeof($header)) {
            $table = '<thead><tr>';
            for ($i = 0; $i < sizeof($header); $i++) {
                $class = Arrays::value($classes, $i, '');
                $h = (is_object($header[$i]) && is_callable($header[$i])) ? $header[$i]() : $header[$i];
                $table .= '<th class="' . Strings::doubleQuoteSlash($class) . '">' . $h . '</th>';
            }
            $table .= '</tr></thead>';
        }
        if (sizeof($footer)) {
            $table = '<tfoot><tr>';
            for ($i = 0; $i < sizeof($footer); $i++) {
                $class = Arrays::value($classes, $i, '');
                $table .= '<td class="' . Strings::doubleQuoteSlash($class) . '">' . $footer[$i] . '</td>';
            }
            $table .= '</tr></tfoot>';
        }
        $table .= '<tbody>';
        for ($i = 0; $i < sizeof($items); $i++) {
            $item = $items[$i];
            $class = ($i % 2 == 0) ? 'even' : 'odd';
            $table .= '<tr class="' . $class . '">';
            for ($j = 0; $j < sizeof($columns); $j++) {
                $class = '';
                $col = $columns[$j];
                if (!is_string($col) && is_callable($col)) {
                    $value = $col($item);
                } else {
                    if (is_array($col)) {
                        $class .= $this->resolveValue($item, Arrays::value($col, 'class')) . ' ';
                        $col = Arrays::value($col, 'label');
                    }
                    $value = $this->resolveValue($item, $col);
                }
                $class .= Arrays::value($classes, $j, '');
                $table .= '<td class="'. Strings::doubleQuoteSlash($class) .'">' . $value . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</tbody>';
        return $this->tag('table', $arg, $table) . '<script type="text/javascript">SGA.DataTable.init("#'. $arg['id'] .'")</script>';
    }
    
    private function resolveValue($item, $key) {
        if (!is_string($key) && is_callable($key)) {
            $value = $key($item);
        } else {
            $params = Strings::getParameters($key);
            // se foi passado uma string com parametros na coluna
            $total = sizeof($params[0]);
            if ($total > 0) {
                $value = $key;
                for ($k = 0; $k < $total; $k++) {
                    $p = Objects::get($item, $params[1][$k]);
                    $value = str_replace($params[0][$k], $p, $value);
                }
            } else {
                // tentando pegar direto no obj
                try {
                    $value = Objects::get($item, $key);
                } catch (Exception $e) {
                    $value = $key;
                }
            }
        }
        return $value;
    }
    
    public function treeView($arg) {
        if (!is_array($arg)) {
            $arg = array();
        }
        $id = Arrays::value($arg, 'id');
        if (empty($id)) {
            $arg['id'] = $this->id('treeview');
        }
        $title = Arrays::value($arg, 'title', '');
        Arrays::removeKey($arg, 'title');
        $buttons = Arrays::value($arg, 'buttons', '');
        Arrays::removeKey($arg, 'buttons');
        $items = Arrays::value($arg, 'items', array());
        Arrays::removeKey($arg, 'items');
        $arg['class'] = Arrays::value($arg, 'class') . ' ui-data-table ui-tree-view';
        $table = '<thead><tr>';
        $table .= '<th class="">#</th>';
        $table .= '<th class="">' . $title . '</th>';
        if (!empty($buttons)) {
            $table .= '<th class=""></th>';
        }
        $table .= '</tr></thead>';
        $table .= '<tbody>';
        $counter = 0;
        $prevs = array();
        for ($i = 0; $i < sizeof($items); $i++) {
            $item = $items[$i];
            if (!($item instanceof \core\model\TreeModel)) {
                throw new Exception(sprintf(_('Valor informado não é instância de TreeModel: %s'), $item));
            }
            if (sizeof($prevs)) {
                $prev = end($prevs);
                if (!$item->isChild($prev)) {
                    while ($prev && !$item->isChild($prev)) {
                        $prev = array_pop($prevs);
                    }
                    $prevs[] = $prev;
                }
                $counter = sizeof($prevs);
            }
            $prevs[] = $item;
            $spacer = '<span class="ui-tree-spacer" style="width:' . ($counter * 25) . 'px"><span class="ui-icon ui-icon-triangle-1-s"></span></span>';
            $class = ($i % 2 == 0) ? 'even' : 'odd';
            $table .= '<tr class="tree-item ' . $class . '" data-left="' . $item->getLeft() . '" data-right="' . $item->getRight() . '" data-open="true">';
            $table .= '<td class="num">' . ($i + 1) . '</td>';
            $table .= '<td class="toggler">' . $spacer . $item->toString() . '</td>';
            if (!empty($buttons)) {
                $value = $this->resolveValue($item, $buttons);
                $table .= '<td class="btns">' . $value . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</tbody>';
        return $this->tag('table', $arg, $table) . '<script type="text/javascript">SGA.TreeView.init("#'. $arg['id'] .'")</script>';
    }
    
}
