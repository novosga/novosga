<?php
use \core\SGA;
use \core\util\Arrays;
?>
<div>
    <div id="tabs">
        <ul>
            <li><a href="#tab-geral"><?php SGA::out(_('Sistema')) ?></a></li>
            <li><a href="#tab-triagem"><?php SGA::out(_('Triagem')) ?></a></li>
            <li><a href="#tab-paineis"><?php SGA::out(_('Painéis')) ?></a></li>
        </ul>
        <div id="tab-geral">
            <fieldset>
                <legend><?php echo _('Autenticação') ?></legend>
                <div class="field">
                    <label for="auth_type" class="w150"><?php echo _('Tipo') ?></label>
                    <?php
                        echo $builder->select(array(
                            'id' => 'auth_type',
                            'default' => Arrays::value($auth, 'type'),
                            'items' => array(
                                'db' => _('Banco de Dados'),
                                'ldap' => _('LDAP e Banco de Dados')
                            )
                        ));
                    ?>
                </div>
                <div id="auth-ldap" class="auth-config" <?php echo ($auth['type'] == 'ldap') ? '' : 'style="display:none"' ?>>
                    <div class="field required">
                        <label for="auth_ldap_host" class="w150"><?php echo _('Servidor') ?></label>
                        <input id="auth_ldap_host" name="host" class="w150" type="text" value="<?php echo Arrays::value($auth['ldap'], 'host') ?>" />
                    </div>
                    <div class="field required">
                        <label for="auth_ldap_port" class="w150"><?php echo _('Porta') ?></label>
                        <input id="auth_ldap_port" name="port" class="w50" type="text" maxlength="6" value="<?php echo Arrays::value($auth['ldap'], 'port') ?>" />
                    </div>
                    <div class="field required">
                        <label for="auth_ldap_host" class="w150"><?php echo _('Base DN') ?></label>
                        <input id="auth_ldap_baseDn" name="baseDn" class="w300" type="text" value="<?php echo Arrays::value($auth['ldap'], 'baseDn') ?>" />
                    </div>
                    <div class="field required">
                        <label for="auth_ldap_loginAttribute" class="w150"><?php echo _('Login Attribute') ?></label>
                        <input id="auth_ldap_loginAttribute" name="loginAttribute" class="w150" type="text" value="<?php echo Arrays::value($auth['ldap'], 'loginAttribute') ?>" />
                    </div>
                    <div class="field">
                        <label for="auth_ldap_user" class="w150"><?php echo _('Usuário') ?></label>
                        <input id="auth_ldap_user" name="username" class="w150" type="text" value="<?php echo Arrays::value($auth['ldap'], 'username') ?>" />
                    </div>
                    <div class="field">
                        <label for="auth_ldap_pass" class="w150"><?php echo _('Senha') ?></label>
                        <input id="auth_ldap_pass" name="password" class="w150" type="password" value="<?php echo Arrays::value($auth['ldap'], 'password') ?>" />
                    </div>
                </div>
                <div class="buttons">
                    <?php
                        echo $builder->button(array(
                            'id' => 'auth_save',
                            'class' => 'ui-button-primary',
                            'label' => _('Salvar'),
                            'onclick' => 'SGA.Admin.Autenticacao.save()'
                        ));
                    ?>
                </div>
            </fieldset>
        </div>
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
                <div class="clear">
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
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script type="text/javascript"> 
        $('#tabs').tabs(); 
        SGA.Admin.Autenticacao.init();
    </script>
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