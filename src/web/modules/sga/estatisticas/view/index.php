<?php
use \core\SGA;
use \core\util\DateUtil;
?>
<div>
    <div id="tabs">
        <ul>
            <li><a href="#tab-hoje"><?php SGA::out(_('Hoje')) ?></a></li>
            <li><a href="#tab-graficos"><?php SGA::out(_('Gráficos')) ?></a></li>
            <li><a href="#tab-relatorios"><?php SGA::out(_('Relatórios')) ?></a></li>
        </ul>
        <div id="tab-hoje">
            <h2 class="chart-title"><?php SGA::out(sprintf(_('Atendimentos realizados em %s'), DateUtil::now(_('d/m/Y')))) ?></h2>
            <?php foreach ($unidades as $unidade): $id = $unidade->getId(); ?>
            <div class="chart-unidade">
                <div class="wrap">
                    <h3 class="title"><?php echo $unidade->getNome() ?></h3>
                    <div id="atendimentos-status-<?php echo $id ?>" class="chart pie atendimentos status">
                        <span class="loading"><?php echo _('Atendimentos por situação') ?></span>
                    </div>
                    <div id="atendimentos-servicos-<?php echo $id ?>" class="chart pie atendimentos status">
                        <span class="loading"><?php echo _('Atendimentos por serviço') ?></span>
                    </div>
                    <script type="text/javascript"> 
                        SGA.Estatisticas.Grafico.today(<?php echo $id ?>, {
                            status: "<?php echo _('Atendimentos por situação') ?>",
                            servicos: "<?php echo _('Atendimentos por serviço') ?>"
                        });
                    </script>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="tab-graficos">
            <form id="chart-form" action="<?php echo SGA::url() ?>" onsubmit="return false">
                <div class="field required">
                    <label for="chart-id"><?php SGA::out(_('Gráfico')) ?></label>
                    <select id="chart-id" name="grafico">
                        <option value=""><?php SGA::out(_('Selecione')) ?></option>
                        <?php foreach ($graficos as $k => $v): ?>
                        <option value="<?php echo $k ?>" data-opcoes="<?php echo $v->getOpcoes() ?>"><?php SGA::out($v->getTitulo()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field required option unidade" style="display:none">
                    <label for="chart-unidade"><?php echo _('Unidade') ?></label>
                    <select id="chart-unidade" name="unidade">
                        <option value="0"><?php echo _('Todas') ?></option>
                        <?php foreach ($unidades as $unidade): ?>
                        <option value="<?php echo $unidade->getId() ?>"><?php echo $unidade->getNome() ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field required option date" style="display:none">
                    <label for="chart-dataInicial"><?php SGA::out(_('Data inicial')) ?></label>
                    <input id="chart-dataInicial" name="inicial" type="text" class="datepicker" value="<?php echo DateUtil::now(_('d/m/Y')) ?>" />
                </div>
                <div class="field required option date" style="display:none">
                    <label for="chart-dataFinal"><?php SGA::out(_('Data final')) ?></label>
                    <input id="chart-dataFinal" name="final" type="text" class="datepicker" value="<?php echo DateUtil::now(_('d/m/Y')) ?>" />
                </div>
                <div class="field">
                    <?php
                        echo $builder->button(array(
                            'type' => 'submit',
                            'class' => 'ui-button-primary',
                            'label' => _('Gerar gráfico'),
                            'onclick' => 'SGA.Estatisticas.Grafico.gerar()'
                        ));
                    ?>
                </div>
                <div id="chart-result"></div>
            </form>
        </div>
        <div id="tab-relatorios">
            <form id="report-form" action="<?php echo SGA::url() ?>" method="get" target="_blank" onsubmit="return SGA.Estatisticas.Relatorio.gerar()">
                <input type="hidden" name="<?php echo SGA::K_MODULE ?>" value="<?php echo SGA::getContext()->getModulo()->getChave() ?>" />
                <input type="hidden" name="<?php echo SGA::K_PAGE ?>" value="relatorio" />
                <input type="hidden" id="report-hidden-inicial" name="inicial" />
                <input type="hidden" id="report-hidden-final" name="final" />
                <div class="field required">
                    <label for="report-id"><?php SGA::out(_('Relatório')) ?></label>
                    <select id="report-id" name="relatorio">
                        <option value=""><?php SGA::out(_('Selecione')) ?></option>
                        <?php foreach ($relatorios as $k => $v): ?>
                        <option value="<?php echo $k ?>" data-opcoes="<?php echo $v->getOpcoes() ?>"><?php echo $v->getTitulo() ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field required option unidade" style="display:none">
                    <label for="report-unidade"><?php echo _('Unidade') ?></label>
                    <select id="report-unidade" name="unidade">
                        <option value="0"><?php echo _('Todas') ?></option>
                        <?php foreach ($unidades as $unidade): ?>
                        <option value="<?php echo $unidade->getId() ?>"><?php echo $unidade->getNome() ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field required option date" style="display:none">
                    <label for="report-dataInicial"><?php SGA::out(_('Data inicial')) ?></label>
                    <input id="report-dataInicial" type="text" class="datepicker" value="<?php echo DateUtil::now(_('d/m/Y')) ?>" />
                </div>
                <div class="field required option date" style="display:none">
                    <label for="report-dataFinal"><?php SGA::out(_('Data final')) ?></label>
                    <input id="report-dataFinal" type="text" class="datepicker" value="<?php echo DateUtil::now(_('d/m/Y')) ?>" />
                </div>
                <div class="field">
                    <?php
                        echo $builder->button(array(
                            'type' => 'submit',
                            'class' => 'ui-button-primary',
                            'label' => _('Gerar relatório')
                        ));
                    ?>
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript"> 
        $('#tabs').tabs(); 
        // unidades
        SGA.Estatisticas.unidades = <?php 
            $arr = array();
            foreach ($unidades as $u) $arr[$u->getId()] = $u->getNome();
            echo json_encode($arr);
        ?>;
        // tab graficos
        $("#chart-id").on('change', function() {
            SGA.Estatisticas.Grafico.change($(this));
        });
        // tab relatorios
        $("#report-id").on('change', function() {
            SGA.Estatisticas.Relatorio.change($(this));
        });
        $(".datepicker" ).datepicker({dateFormat: '<?php echo _('dd/mm/yy') ?>'});
        SGA.Form.validate('report-form');
    </script>
</div>
