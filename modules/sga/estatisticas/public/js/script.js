/**
 * Novo SGA - Estatisticas
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

SGA.Estatisticas = {
    
    unidades: {},
            
    options: function(group) {
        var elems = $(group + ' .option');
        elems.find(':input').prop('disabled', true);
        elems.hide();
        // habilitando as opções do gráfico/relatório selecionado
        var param = $(group + ' option:selected').data('opcoes');
        if (param != '') {
            var opcoes = param.split(',');
            for (var i = 0; i < opcoes.length; i++) {
                elems = $(group + ' .' + opcoes[i]);
                elems.find(':input').prop('disabled', false);
                elems.show();
            }
        }
    },
    
    Grafico: {

        today: function(unidade, titulos) {
            SGA.ajax({
                url: SGA.url('today'),
                data: {
                    unidade: unidade
                },
                success: function(response) {
                    SGA.Estatisticas.Grafico.pie({
                        id: 'atendimentos-status-' + unidade, 
                        dados: response.data.status,
                        legendas: response.data.legendas,
                        titulo: titulos.status
                    });
                    SGA.Estatisticas.Grafico.pie({
                        id: 'atendimentos-servicos-' + unidade, 
                        dados: response.data.servicos,
                        titulo: titulos.servicos
                    });
                }
            });
        },
        
        gerar: function() {
            var id = $('#chart-id').val();
            if (id > 0) {
                var dtIni = $('#chart-dataInicial').val();
                var dtFim = $('#chart-dataFinal').val();
                SGA.ajax({
                    url: SGA.url('grafico'),
                    data: {
                        grafico: id, 
                        unidade: ($('#chart-unidade').prop('disabled') ? 0 : $('#chart-unidade').val()), 
                        inicial: SGA.dateToSql(dtIni), 
                        'final': SGA.dateToSql(dtFim)
                    },
                    success: function(response) {
                        var result = $('#chart-result');
                        result.html('');
                        var dados = response.data.dados;
                        for (var i in dados) {
                            if (SGA.Estatisticas.unidades[i]) {
                                var id = 'chart-result-' + i;
                                result.append('<div id="' + id + '"></div>');
                                var prop = {
                                    id: id, 
                                    dados: response.data.dados[i],
                                    legendas: response.data.legendas,
                                    titulo: response.data.titulo + ' - ' + SGA.Estatisticas.unidades[i] + ' (' + dtIni + ' - ' + dtFim + ')'
                                };
                                switch (response.data.tipo) {
                                case 'pie':
                                    SGA.Estatisticas.Grafico.pie(prop);
                                    break;
                                case 'bar':
                                    SGA.Estatisticas.Grafico.bar(prop);
                                    break;
                                }
                            }
                        }
                        $(window).scrollTop($('#chart-result').position().top);
                    }
                });
            }
        },
        
        change: function(elem) {
            if (elem.val() > 0) {
                // desabilitando as opções
                SGA.Estatisticas.options('#tab-graficos');
            }
        },
        
        pie: function(prop) {
            var series = [];
            for (var j in prop.dados) {
                var legenda = prop.legendas && prop.legendas[j] ? prop.legendas[j] : j;
                series.push([legenda, parseInt(prop.dados[j])]);
            }
            new Highcharts.Chart({
                chart: {
                    renderTo: prop.id,
                    type: 'pie'
                },
                title: { 
                    text: prop.titulo 
                },
                plotOptions: {
                    pie: {
                        showInLegend: true,
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return '<b>' + this.point.name + '</b>: ' + Math.round(this.point.total * this.point.percentage / 100);
                            }
                        }
                    }
                },
                series: [{
                    type: 'pie',
                    name: prop.titulo,
                    data: series
                }],
                exporting: {
                    enabled: true,
                    sourceWidth: 1024,
                    sourceHeight: 800
                }
            });
        },
        
        bar: function(prop) {
            var series = [];
            var categories = [];
            for (var j in prop.dados) {
                var legenda = prop.legendas && prop.legendas[j] ? prop.legendas[j] : j;
                series.push({
                    name: legenda, 
                    data: [parseInt(prop.dados[j])]
                });
                categories.push(legenda);
            }
            new Highcharts.Chart({
                chart: {
                    renderTo: prop.id,
                    type: 'bar'
                },
                title: { 
                    text: prop.titulo 
                },
                xAxis: {
                    categories: categories,
                    title: {
                        text: null
                    }
                },
                // TODO: informar no response o tipo de tooltip (abaixo esta fixo formatando tempo)
                tooltip: {
                    formatter: function() {
                        return this.series.name + ': ' + SGA.secToTime(this.y);
                    }
                },
                series: series
            });
        }
        
    },
    
    Relatorio: {
        
        gerar: function() {
            $('#report-hidden-inicial').val(SGA.dateToSql($('#report-dataInicial').val()));
            $('#report-hidden-final').val(SGA.dateToSql($('#report-dataFinal').val()));
            return true;
        },
        
        change: function(elem) {
            if (elem.val() > 0) {
                // desabilitando as opções
                SGA.Estatisticas.options('#tab-relatorios');
            }
        }
        
    }
    
};
