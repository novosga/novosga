<?php
use \core\SGA;
?>
<div>
    <div id="tabs">
        <ul>
            <li><a href="#tabs-triagem"><?php echo _('Triagem') ?></a></li>
            <li><a href="#tabs-servicos"><?php echo _('Serviços') ?></a></li>
        </ul>
        <div id="tabs-triagem">
            <form action="<?php echo SGA::url(array(SGA::K_PAGE => 'update_impressao')) ?>" method="post">
                <fieldset>
                    <legend><?php echo _('Impressão') ?></legend>
                    <div class="field checkbox">
                        <?php 
                        echo $builder->checkbox(array(
                            'id' => 'impressao',
                            'name' => 'impressao',
                            'value' => '1',
                            'checked' => $unidade->getStatusImpressao()
                        ));
                        ?>
                        <label for="impressao"><?php echo _('Ativar impressão de senha') ?></label>
                    </div>
                    <div class="field">
                        <label><?php echo _('Mensagem') ?></label>
                        <input type="text" id="mensagem" name="mensagem" class="w400" maxlength="100" value="<?php echo $unidade->getMensagemImpressao() ?>" />
                    </div>
                    <div class="field">
                        <label><?php echo _('Reiniciar senhas') ?></label>
                        <?php echo $builder->button(array('label' => 'Reiniciar', 'class' => 'ui-button-error')) ?>
                    </div>
                </fieldset>
                <div class="buttons">
                    <?php echo $builder->button(array('label' => 'Salvar', 'class' => 'ui-button-primary')) ?>
                </div>
            </form>
        </div>
        <div id="tabs-servicos">
            <?php
            $sigla = function($model) {
                $id = $model->getServico()->getId();
                $disabled = ($model->getStatus() != 1) ? 'disabled="disabled"' : '';
                return '<input id="sigla-'. $id .'" type="text" class="w25 center" value="' . $model->getSigla() . '" data-id="' . $id .'" onclick="this.select()" onkeyup="this.value=this.value.toUpperCase()" onchange="SGA.Unidade.Servicos.updateSigla(this)" onblur="SGA.Unidade.Servicos.updateSigla(this)" maxlength="1" ' . $disabled . '/>';
            };
            $status = function($model) use ($builder) {
                $id = $model->getServico()->getId();
                $ativar = array(
                    'id' => 'btn-enable-' . $id,
                    'class' => 'btn-enable ui-button-success',
                    'data-id' => $id,
                    'label' => _('Habilitar'),
                    'onclick' => 'SGA.Unidade.Servicos.enable(this)'
                );
                $desativar = array(
                    'id' => 'btn-disable-' . $id,
                    'class' => 'btn-disable ui-button-error',
                    'data-id' => $id,
                    'label' => _('Desabilitar'),
                    'onclick' => 'SGA.Unidade.Servicos.disable(this)'
                );
                if ($model->getStatus() == 1) {
                    $ativar['disabled'] = 'disabled';
                } else {
                    $desativar['disabled'] = 'disabled';
                }
                return '<span class="btns-status">' . $builder->button($ativar) . $builder->button($desativar) . '</span>';
            };
            $table = $builder->table(array(
                'header' => array(
                    _('Sigla'), _('Serviço'), _('Status')
                ),
                'classes' => array(
                    'sigla', '', 'btns'
                ),
                'columns' => array(
                    $sigla, 'nome', $status
                ),
                'items' => $servicos
            ));
            echo $table;
            ?>
        </div>
    </div>
    <script type="text/javascript"> $('#tabs').tabs(); </script>
</div>