<?php
use \core\SGA;
$modulo = SGA::getContext()->getModulo();
$unidade = SGA::getContext()->getUnidade();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $modulo->getNome() ?></title>
    <?php $this->headerDependencies(SGA::getContext()); ?>
    <script type="text/javascript" src="<?php echo $modulo->getPath() . '/js/script.js?v=' . SGA::VERSION ?>"></script>
    <link type="text/css" rel="stylesheet" href="<?php echo $modulo->getPath() . '/css/touch.css?v=' . SGA::VERSION ?>" />
</head>
<body>
    <div id="touchscreen">
        <div id="touchscreen-header">
            <h1><?php echo $unidade->getNome() ?></h1>
            <p><?php echo _('Escolha abaixo o serviço que deseja ser atendido') ?></p>
            <a id="btn-fullscreen" href="javascript:void(0)" onclick="fullscreen()" title="<?php echo _('Alterar para modo tela cheia') ?>">fullscreen</a>
        </div>
        <div id="touchscreen-body">
            <div id="page-servicos" class="page">
                <?php 
                    foreach ($servicos as $su) {
                        echo '<div class="btn-touch col-3">' . $builder->button(array(
                            'class' => 'ui-button-primary btn-servico',
                            'label' => $su->getNome(),
                            'onclick' => 'SGA.Triagem.Touchscreen.chooseServico(' . $su->getServico()->getId() . ')'
                        )) . '</div>'; 
                    } 
                ?>
            </div>
            <div id="page-tipo" class="page" style="display:none">
                <?php
                
                echo '<div class="btn-touch col-2">' . $builder->button(array(
                    'class' => 'ui-button-primary btn-tipo',
                    'label' => _('Atendimento Normal'),
                    'onclick' => 'SGA.Triagem.Touchscreen.chooseAtendimentoNormal()'
                )) . '</div>';
                
                echo '<div class="btn-touch col-2">' . $builder->button(array(
                    'class' => 'ui-button-error btn-tipo',
                    'label' => _('Atendimento Prioritário'),
                    'onclick' => 'SGA.Triagem.Touchscreen.chooseAtendimentoPrioridade()'
                )) . '</div>';
                
                ?>
                <div class="btn-voltar">
                    <?php
                        echo $builder->button(array(
                            'label' => _('Voltar'),
                            'onclick' => 'SGA.Triagem.Touchscreen.inicio()',
                            'icon' => 'ui-icon-arrowreturnthick-1-w'
                        ));
                    ?>
                </div>
            </div>
            <div id="page-prioridades" class="page" style="display:none">
                <?php 
                    foreach ($prioridades as $prioridade) {
                        echo '<div class="btn-touch col-2">' . $builder->button(array(
                            'class' => 'ui-button-error btn-prioridade',
                            'label' => $prioridade->getNome(),
                            'onclick' => 'SGA.Triagem.Touchscreen.choosePrioridade(' . $prioridade->getId() . ')'
                        )) . '</div>';
                    }
                ?>
                <div class="btn-voltar">
                    <?php
                        echo $builder->button(array(
                            'label' => _('Voltar'),
                            'onclick' => 'SGA.Triagem.Touchscreen.inicio()',
                            'icon' => 'ui-icon-arrowreturnthick-1-w'
                        ));
                    ?>
                </div>
            </div>
            <div id="page-fim" class="page" style="display:none">
                <div id="senha-gerada">
                    <span id="senha-servico">Macro 1</span>
                    <span id="senha-numero">A0024</span>
                    <span id="senha-tipo">Sem prioridade</span>
                </div>
                <div class="btn-voltar">
                    <?php
                        echo $builder->button(array(
                            'label' => _('Voltar'),
                            'onclick' => 'SGA.Triagem.Touchscreen.inicio()',
                            'icon' => 'ui-icon-arrowreturnthick-1-w'
                        ));
                    ?>
                </div>
            </div>
        </div>
    </div>
    <iframe id="frame-impressao" width="300" height="150"></iframe>
    <script type="text/javascript">
        SGA.Triagem.imprimir = <?php echo ($unidade->getStatusImpressao() ? 'true' : 'false') ?>;
        function fullscreen() {
            SGA.FullScreen.change(function() {
                if (SGA.FullScreen.element()) {
                    $('#btn-fullscreen').hide();
                } else {
                    $('#btn-fullscreen').show();
                }
            });
            SGA.FullScreen.request(document.getElementById("touchscreen"));
        }
    </script>
</body>
</html>