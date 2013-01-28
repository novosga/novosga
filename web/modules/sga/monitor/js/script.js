/**
 * Novo SGA - Monitor
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Monitor = {

    ids: [],
    ajaxInterval: 3000,
    labelTransferir: '',
    alertCancelar: '',
    
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
                                        var atendimento = fila[j];
                                        var onclick = "SGA.Monitor.Senha.view(" + atendimento.id + ")";
                                        var item = '<li class="' + (atendimento.prioridade ? 'prioridade' : '') + '">';
                                        var title = atendimento.nomePrioridade;
                                        item += '<a href="javascript:void(0)" onclick="' + onclick + '" title="' + title + '">' + atendimento.senha + '</a>';
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
        
        dialogView: '#dialog-view',
        dialogSearch: '#dialog-busca',
        dialogTransfere: '#dialog-transfere',
        situacoes: {},
    
        /**
         * Busca informacoes do atendimento pelo id.
         */
        view: function(id) {
            SGA.ajax({
                url: SGA.url('info_senha'),
                data: {id: id},
                success: function(response) {
                    if (response.success) {
                        var dialog = $(SGA.Monitor.Senha.dialogView);
                        dialog.find('#senha_id').val(response.data.id);
                        dialog.find('#senha_numero').text(response.data.senha);
                        dialog.find('#senha_prioridade').text(response.data.nomePrioridade);
                        dialog.find('#senha_servico').text(response.data.servico);
                        dialog.find('#senha_chegada').text(SGA.formatDate(response.data.chegada));
                        dialog.find('#senha_inicio').text(SGA.formatDate(response.data.inicio));
                        dialog.find('#senha_fim').text(SGA.formatDate(response.data.fim));
                        dialog.find('#senha_status').text(SGA.Monitor.Senha.situacoes[response.data.status]);
                        dialog.find('#cliente_nome').text(response.data.cliente.nome);
                        dialog.find('#cliente_documento').text(response.data.cliente.documento);
                        // so pode transferir ou cancelar se o status for 1 (senha emitida)
                        var status = response.data.status == 1 ? 'enable' : 'disable';
                        $('#btn-transferir').button(status);
                        $('#btn-cancelar').button(status);
                        SGA.dialogs.modal(dialog, { width: 600 });
                    }
                }
            });
        },
        
        consulta: function() {
            var numero = $('#buscar-senha').val();
            try {
                numero = parseInt(numero);
                if (numero > 0) {
                    SGA.dialogs.modal(SGA.Monitor.Senha.dialogSearch, { 
                        width: 900,
                        open: function() {
                            $('#numero_busca').val(numero);
                            SGA.Monitor.Senha.consultar();
                        }
                    });
                }
            } catch (e) {
            }
            $('#buscar-senha').val('');
        },
        
        consultar: function() {
            SGA.ajax({
                url: SGA.url('buscar'),
                data: {numero: $('#numero_busca').val()},
                success: function(response) {
                    var result = $('#result_table tbody');
                    result.html('');
                    if (response.data.total > 0) {
                        for (var i = 0; i < response.data.total; i++) {
                            var atendimento = response.data.atendimentos[i];
                            var tr = '<tr>';
                            tr += '<td><a href="javascript:void(0)" onclick="SGA.Monitor.Senha.view(' + atendimento.id + ')">' + atendimento.senha + '</a></td>';
                            tr += '<td>' + atendimento.servico + '</td>';
                            tr += '<td>' + SGA.formatDate(atendimento.chegada) + '</td>';
                            tr += '<td>' + SGA.formatDate(atendimento.inicio) + '</td>';
                            tr += '<td>' + SGA.formatDate(atendimento.fim) + '</td>';
                            tr += '<td>' + SGA.Monitor.Senha.situacoes[atendimento.status] + '</td>';
                            tr += '</tr>';
                            result.append(tr);
                        }
                    }
                }
            });
        },

        transfere: function(id, numero) {
            var buttons = {};
            buttons[SGA.Monitor.labelTransferir] = function() {
                SGA.Monitor.Senha.transferir();
            };
            SGA.dialogs.modal(SGA.Monitor.Senha.dialogTransfere, {
                buttons: buttons
            });
            $('#transfere_id').val(id);
            $('#transfere_numero').text(numero);
        },

        transferir: function() {
            SGA.ajax({
                url: SGA.url('transferir'),
                data: {
                    id: $('#transfere_id').val(),
                    servico: $('#transfere_servico').val(),
                    prioridade: $('#transfere_prioridade').val()
                },
                complete: function() {
                    $(SGA.Monitor.Senha.dialogView).dialog('close');
                    $(SGA.Monitor.Senha.dialogTransfere).dialog('close');
                }
            });
        },

        reativar: function() {

        },

        cancelar: function(id) {
            if (window.confirm(SGA.Monitor.alertCancelar)) {
                SGA.ajax({
                    url: SGA.url('cancelar'),
                    data: { id: id },
                    complete: function() {
                        $(SGA.Monitor.Senha.dialogView).dialog('close');
                        $(SGA.Monitor.Senha.dialogTransfere).dialog('close');
                    }
                });
            }
        }
    }
    
};