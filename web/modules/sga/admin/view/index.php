<?php
use \core\SGA;
?>
<div>
    <div id="tabs">
        <ul>
            <li><a href="#tab-triagem"><?php SGA::out(_('Triagem')) ?></a></li>
            <li><a href="#tab-paineis"><?php SGA::out(_('Painéis')) ?></a></li>
        </ul>
        <div id="tab-triagem">
            <div class="field">
                <label><?php SGA::out(_('Reiniciar senhas')) ?></label>
                <?php 
                    echo $builder->button(array(
                        'label' => 'Reiniciar', 
                        'class' => 'ui-button-error',
                        'onclick' => "return SGA.Admin.reiniciarSenhas('". _('Deseja realmente reiniciar as senhas de todas unidades?') ."')"
                    )) 
                ?>
            </div>
        </div>
        <div id="tab-paineis">
            <?php foreach ($unidades as $unidade): ?>
                <h3><?php SGA::out($unidade->getNome()) ?></h3>
                <ul class="paineis">
                    <?php 
                        if (isset($paineis[$unidade->getId()])):
                            foreach ($paineis[$unidade->getId()] as $painel): 
                            ?>
                            <li>
                                <a href="javascript:void(0)" onclick="SGA.Admin.painelInfo(<?php echo $unidade->getId() ?>, <?php echo $painel->getHost() ?>)" title="<?php SGA::out(_('Visualizar serviços')) ?>">
                                    <span>IP</span>
                                    <span class="ip"><?php echo $painel->getIp() ?></span>
                                </a>
                            </li>
                        <?php 
                            endforeach; 
                        endif; 
                    ?>
                </ul>
            <?php endforeach; ?>
        </div>
    </div>
    <script type="text/javascript"> $('#tabs').tabs(); </script>
</div>
<div id="dialog-reiniciar" title="<?php SGA::out(_('Configuração')) ?>" style="display:none">
    <p><?php SGA::out(_('Senhas reiniciadas com sucesso')) ?></p>
</div>
<div id="dialog-painel" title="<?php SGA::out(_('Painel')) ?>" style="display:none">
    <div>
        <label>IP</label>
        <span id="painel_ip"></span>
    </div>
    <div>
        <label><?php SGA::out(_('Unidade')) ?></label>
        <span id="painel_unidade"></span>
    </div>
    <div>
        <label><?php SGA::out(_('Serviços')) ?></label>
        <ul id="painel_servicos"></ul>
    </div>
    <div>
        <label><?php SGA::out(_('Últimas senhas')) ?></label>
        <ul id="painel_senhas"></ul>
    </div>
</div>