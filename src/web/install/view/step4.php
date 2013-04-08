<?php
use \core\SGA;
use \install\InstallData;
use \core\view\TemplateBuilder;

$context = SGA::getContext();
$session = $context->getSession();
$data = $session->get(InstallData::SESSION_KEY);
if (!$data) {
    $data = new InstallData();
    $session->set(InstallData::SESSION_KEY, $data);
}

$currVersion = $context->getParameter('currVersion');

?>
<div id="step_4">
    <?php if ($currVersion): ?>
    <h2>Uma versão já instalada do SGA foi identificada.</h2>
    <p>Foi identificado que o banco de dados atual possui a versão "<?php echo $currVersion ?>" do SGA.</p>
    <p>O instalador irá executar os scripts de migração para atualizar o banco. Caso você não queira atualizar, certifique-se que o banco esteja vazio e recarregue essa página.</p>
    <script type="text/javascript">SGA.Install.isMigration = true;</script>
    <?php else: ?>
    <fieldset>
        <legend>Administrador</legend>
        <?php
            $builder = new TemplateBuilder();
            echo $builder->error(array(
                'id' => 'db_admin_error',
                'style' => 'display:none'
            ));
        ?>
        <div class="field">
            <label class="w125">Nome:</label>
            <input type="text" id="nm_usu" name="nm_usu" />
        </div>
        <div class="field">
            <label class="w125">Sobrenome:</label>
            <input type="text" id="ult_nm_usu" name="ult_nm_usu" />
        </div>
        <div class="field">
            <label class="w125">Email:</label>
            <input type="text" id="email_usu" name="email_usu" />
        </div>
        <div class="field">
            <label class="w125">Usuário:</label>
            <input type="text" id="login_usu" name="login_usu" onkeyup="SGA.Form.loginValue(this)" maxlength="20" />
        </div>
        <div class="field">
            <label class="w125">Senha:</label>
            <input type="password" id="senha_usu" name="senha_usu" />
        </div>
        <div class="field">
            <label class="w125">Confirmar Senha:</label>
            <input type="password" id="senha_usu_2" name="senha_usu_2" />
        </div>
        <script type="text/javascript">
            <?php
                // lendo dados da sessao
                foreach ($data->admin as $field => $message) {
                    echo 'SGA.Install.adminData.' . $field . ' = "' . $data->admin[$field] . '"; ';
                }
            ?>
            SGA.Install.loadAdminData();
        </script>
    </fieldset>
    <?php endif; ?>
</div>