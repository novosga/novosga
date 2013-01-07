/**
 * Novo SGA - Monitor
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Monitor = {

    ids: [],
    ajaxInterval: 3000,
    atendimentoNormal: '',
    
    init: function() {
        setInterval(SGA.Monitor.ajaxUpdate, SGA.updateInterval);
    },
    
    ajaxUpdate: function() {
        if (!SGA.paused) {
            SGA.ajax({
                url: SGA.url('ajax_update'),
                data: {ids: SGA.Monitor.ids.join(',')},
                success: function(response) {
                    if (response.success) {
                        $('#monitor .servico').hide();
                        if (response.data.total > 0) {
                            for (var i in response.data.servicos) {
                                var fila = response.data.servicos[i];
                                var servico = $('#servico-' + i);
                                if (fila.length > 0) {
                                    servico.show();
                                    var list = servico.find('.fila');
                                    list.text('');
                                    for (var j = 0; j < fila.length; j++) {
                                        var senha = fila[j];
                                        var onclick = "SGA.Monitor.Senha.view(" + senha.numero + ")";
                                        var item = '<li class="' + (senha.prioridade ? 'prioridade' : '') + '">';
                                        var title = senha.prioridade ? senha.nomePrioridade : SGA.Monitor.atendimentoNormal;
                                        item += '<a href="javascript:void(0)" onclick="' + onclick + '" title="' + title + '">' + senha.numero_full + '</a>';
                                        item += '</li>';
                                        list.append(item);
                                    }
                                } else {
                                    servico.hide();
                                }
                            }
                        }
                    }
                }
            });
        }
    },
    
    Senha: {
        
        dialogView: 'dialog-monitor',
        dialogSearch: 'dialog-busca',
    
        view: function(senha) {
            SGA.ajax({
                url: SGA.url('info_senha'),
                data: {numero: senha},
                success: function(response) {
                    if (response.success) {
                        var dialog = $('#' + SGA.Monitor.Senha.dialogView);
                        dialog.find('#senha_numero').text(response.data.numero);
                        dialog.find('#senha_prioridade').text(response.data.prioridade);
                        dialog.find('#senha_servico').text(response.data.servico);
                        dialog.find('#senha_chegada').text(SGA.formatDate(response.data.chegada));
                        dialog.find('#cliente_nome').text(response.data.cliente.nome);
                        dialog.find('#cliente_documento').text(response.data.cliente.documento);
                        SGA.dialogs.modal(dialog, { width: 600 });
                    }
                }
            });
        },
        
        consultar: function() {
            SGA.dialogs.modal('#' + SGA.Monitor.Senha.dialogSearch, { width: 600 });
        },

        reativar: function() {

        },

        cancelar: function() {

        }
    }
    
};