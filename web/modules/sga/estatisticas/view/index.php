<?php
use \core\SGA;
use core\contrib\Highcharts;
use core\contrib\Serie;
?>
<div>
    <div id="tabs">
        <ul>
            <li><a href="#tabs-hoje"><?php SGA::out(_('Hoje')) ?></a></li>
            <li><a href="#tabs-graficos"><?php SGA::out(_('Gráficos')) ?></a></li>
            <li><a href="#tabs-relatorios"><?php SGA::out(_('Relatórios')) ?></a></li>
        </ul>
        <div id="tabs-hoje">
            <h2 class="chart-title"><?php SGA::out(_('Atendimentos')) ?></h2>
            <?php 
            foreach ($unidades as $unidade) {
                $id = $unidade->getId();
                $script = '';
                if (isset($atendimentos[$id])) {
                    $atendimento = $atendimentos[$id];
                    $chart = new Highcharts('atendimentos-' . $id, _('Atendimentos'));
                    $chart->setType('pie');
                    $data = array();
                    $data[] = array(_('Total'), (int) $atendimento['total']);
                    $data[] = array(_('Encerrado'), (int) $atendimento['encerrado']);
                    $chart->addSerie(new Serie('Atendimentos', $data));
                    $script = $chart->toString();
                }
                ?>
                <div class="unidade">
                    <div class="wrap">
                        <h3 class="title"><?php SGA::out($unidade->getNome()) ?></h3>
                        <div id="atendimentos-<?php SGA::out($id) ?>" class="chart pie atendimentos"></div>
                        <?php SGA::out($script) ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div id="tabs-graficos">
            
        </div>
        <div id="tabs-relatorios">
        </div>
    </div>
    <script type="text/javascript"> $('#tabs').tabs(); </script>
</div>