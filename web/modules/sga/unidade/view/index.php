<?php
use \core\SGA;
?>
<div>
    <div id="tabs">
        <ul>
            <li><a href="#tab-triagem"><?php SGA::out(_('Triagem')) ?></a></li>
            <li><a href="#tab-servicos"><?php SGA::out(_('Serviços')) ?></a></li>
        </ul>
        <div id="tab-triagem">
            <form action="<?php SGA::out(SGA::url(array(SGA::K_PAGE => 'update_impressao'))) ?>" method="post">
                <fieldset>
                    <legend><?php SGA::out(_('Impressão')) ?></legend>
                    <div class="field checkbox">
                        <?php 
                        echo $builder->checkbox(array(
                            'id' => 'impressao',
                            'name' => 'impressao',
                            'value' => '1',
                            'checked' => $unidade->getStatusImpressao()
                        ));
                        ?>
                        <label for="impressao"><?php SGA::out(_('Ativar impressão de senha')) ?></label>
                    </div>
                    <div class="field">
                        <label><?php SGA::out(_('Mensagem')) ?></label>
                        <input type="text" id="mensagem" name="mensagem" class="w400" maxlength="100" value="<?php SGA::out($unidade->getMensagemImpressao()) ?>" />
                    </div>
                    <div class="field">
                        <label><?php SGA::out(_('Reiniciar senhas')) ?></label>
                        <?php 
                            echo $builder->button(array(
                                'label' => 'Reiniciar', 
                                'class' => 'ui-button-error',
                                'onclick' => "return SGA.Unidade.reiniciarSenhas('". _('Deseja realmente reiniciar as senhas?') ."')"
                            )) 
                        ?>
                    </div>
                </fieldset>
                <div class="buttons">
                    <?php echo $builder->button(array('label' => 'Salvar', 'class' => 'ui-button-primary')) ?>
                </div>
            </form>
        </div>
        <div id="tab-servicos">
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
<div id="dialog-reiniciar" title="<?php SGA::out(_('Configuração')) ?>" style="display:none">
    <p><?php SGA::out(_('Senhas reiniciadas com sucesso')) ?></p>
</div>