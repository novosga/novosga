<?php
use \core\SGA;
use \core\view\TemplateBuilder;
?>
<div id="step_5">
    <h1>Instalar</h1>
    <?php
        $builder = new TemplateBuilder();
        echo $builder->error(array(
            'id' => 'install_error',
            'style' => 'display:none'
        ));
        echo $builder->success(array(
            'id' => 'install_success',
            'style' => 'display:none'
        ));
    ?>
    <p><?php SGA::out(_('Clique em Instalar para iniciar o processo de instalação do sistema.')) ?></p>
    <p><strong><?php SGA::out(_('Atenção')) ?></strong>: <?php SGA::out(_('Ao clicar em instalar, caso exista uma instalação do SGA Livre no banco especificado a mesma será sobrescrita.')) ?></p>
    <div class="btns">
        <?php 
            echo $builder->button(array(
                'id' => 'btn_install_final',
                'label' => 'Instalar', 
                'onclick' => 'SGA.Install.doInstall()',
                'class' => 'ui-button-success',
                'title' => 'Clique para iniciar o processo final de instalação.'
            ));
            echo $builder->button(array(
                'id' => 'btn_redirect',
                'label' => 'Ir para tela de login', 
                'onclick' => "window.location = './'",
                'class' => 'ui-button-primary',
                'title' => 'Clique para iniciar a utilização do SGA.',
                'style' => 'display:none'
            ));
        ?>
    </div>
</div>