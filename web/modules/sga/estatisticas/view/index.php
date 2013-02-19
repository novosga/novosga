<?php
use \core\SGA;
use \core\util\DateUtil;
use \core\contrib\Highcharts;
use \core\contrib\Serie;
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
            <?php 
            foreach ($unidades as $unidade) {
                $id = $unidade->getId();
                $script = '';
                if (isset($atendimentosStatus[$id])) {
                    $chart = new Highcharts('atendimentos-status-' . $id, _('Atendimentos por situação'));
                    $chart->setType('pie');
                    $data = array();
                    $atendimentos = $atendimentosStatus[$id];
                    foreach ($atendimentos as $k => $v) {
                        $data[] = array($statusAtendimento[$k], (int) $v);
                    }
                    $chart->addSerie(new Serie('Atendimentos', $data));
                    $script .= '<div id="' . $chart->getId() .'" class="chart pie atendimentos status"></div>';
                    $script .= $chart->toString();
                }
                if (isset($atendimentosServico[$id])) {
                    $atendimentos = $atendimentosServico[$id];
                    $chart = new Highcharts('atendimentos-servico-' . $id, _('Atendimentos por serviço'));
                    $chart->setType('pie');
                    $data = array();
                    foreach ($atendimentos as $k => $v) {
                        $data[] = array($k, (int) $v);
                    }
                    $chart->addSerie(new Serie('Atendimentos', $data));
                    $script .= '<div id="' . $chart->getId() .'" class="chart pie atendimentos servico"></div>';
                    $script .= $chart->toString();
                }
                ?>
                <div class="unidade">
                    <div class="wrap">
                        <h3 class="title"><?php SGA::out($unidade->getNome()) ?></h3>
                        <?php echo $script ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div id="tab-graficos">
            <form id="chart-form" action="<?php echo SGA::url() ?>" onsubmit="return false">
                <div class="field required">
                    <label for="chart-id"><?php SGA::out(_('Gráfico')) ?></label>
                    <select id="chart-id" name="grafico">
                        <option value=""><?php SGA::out(_('Selecione')) ?></option>
                        <?php foreach ($graficos as $k => $v): ?>
                        <option value="<?php echo $k ?>"><?php SGA::out($v->getTitulo()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field required date">
                    <label for="chart-dataInicial"><?php SGA::out(_('Data inicial')) ?></label>
                    <input id="chart-dataInicial" name="inicial" type="text" class="datepicker" value="<?php echo DateUtil::now(_('d/m/Y')) ?>" />
                </div>
                <div class="field required date">
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
                        <option value="<?php echo $k ?>"><?php SGA::out($v->getTitulo()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field required date" style="display:none">
                    <label for="report-dataInicial"><?php SGA::out(_('Data inicial')) ?></label>
                    <input id="report-dataInicial" type="text" class="datepicker" value="<?php echo DateUtil::now(_('d/m/Y')) ?>" />
                </div>
                <div class="field required date" style="display:none">
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
        
        // tab relatorios
        $("#report-id").on('change', function() {
            SGA.Estatisticas.Relatorio.change($(this));
        });
        $(".datepicker" ).datepicker({dateFormat: '<?php echo _('dd/mm/yy') ?>'});
        SGA.Form.validate('report-form');
    </script>
</div>
