<?php
use \core\SGA;
?>
<div>
    <input id="modulo_id" type="hidden" value="<?php echo $modulo->getId() ?>" />
    <div id="tabs">
        <ul>
            <li><a href="#tab-sobre"><?php SGA::out(_('Sobre')) ?></a></li>
            <li><a href="#tab-css"><?php SGA::out(_('CSS')) ?></a></li>
            <li><a href="#tab-javascript"><?php SGA::out(_('Javascript')) ?></a></li>
        </ul>
        <div id="tab-sobre">
            <div class="info">
                <div class="icon">
                    <?php echo $builder->tag('img', array('src' => $modulo->getPath() . DS . 'icon.png')) ?>
                </div>
                <div class="text nome">
                    <label><?php SGA::out(_('Nome')) ?></label>
                    <span><?php SGA::out($modulo->getNome()) ?></span>
                </div>
                <div class="text autor">
                    <label><?php SGA::out(_('Autor')) ?></label>
                    <span><?php SGA::out($modulo->getAutor()) ?></span>
                </div>
                <div class="text chave">
                    <label><?php SGA::out(_('Chave')) ?></label>
                    <span><?php SGA::out($modulo->getChave()) ?></span>
                </div>
                <div class="text descricao">
                    <label><?php SGA::out(_('Descrição')) ?></label>
                    <p><?php SGA::out($modulo->getDescricao()) ?></p>
                </div>
            </div>
        </div>
        <div id="tab-css">
            <div class="resource-buttons">
                <h4 class="css">CSS</h4>
                <?php 
                echo $builder->button(array(
                    'icon' => 'ui-icon-disk',
                    'label' => _('Salvar'),
                    'onclick' => "SGA.Modulos.Resource.save('css')"
                ));
                echo $builder->button(array(
                    'icon' => 'ui-icon-arrowrefresh-1-w',
                    'label' => _('Recarregar'),
                    'onclick' => "SGA.Modulos.Resource.load('css')"
                ));
                ?>
            </div>
            <textarea id="css" class="w100pct h500"><?php SGA::out($css) ?></textarea>
        </div>
        <div id="tab-javascript">
            <div class="resource-buttons">
                <h4 class="js">Javascript</h4>
                <?php 
                echo $builder->button(array(
                    'icon' => 'ui-icon-disk',
                    'label' => _('Salvar'),
                    'onclick' => "SGA.Modulos.Resource.save('js')"
                ));
                echo $builder->button(array(
                    'icon' => 'ui-icon-arrowrefresh-1-w',
                    'label' => _('Recarregar'),
                    'onclick' => "SGA.Modulos.Resource.load('js')"
                ));
                ?>
            </div>
            <textarea id="js" class="w100pct h500"><?php SGA::out($javascript) ?></textarea>
        </div>
    </div>
    <div class="buttons">
        <?php 
            echo $builder->button(array(
                'type' => 'link',
                'label' => _('Voltar'),
                'href' => SGA::url('index'),
                'class' => 'btn-back'
            ));
        ?>
    </div>
</div>
<script type="text/javascript">
    $('#tabs').tabs();
</script>