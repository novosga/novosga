<?php
use \core\SGA;
use \install\InstallData;
use \core\view\TemplateBuilder;

$session = SGA::getContext()->getSession();
$data = $session->get(InstallData::SESSION_KEY);
if (!$data) {
    $data = new InstallData();
    $session->set(InstallData::SESSION_KEY, $data);
}

?>
<div id="step_4">
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
            <label class="w125">Usuário:</label>
            <input type="text" id="login_usu" name="login_usu" />
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
</div>