/**
 * Novo SGA - Monitor
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

App.Monitor = {

    ids: [],
    labelTransferir: '',
    alertCancelar: '',
    alertReativar: '',
    timeoutId: 0,
    
    init: function() {
        App.Monitor.ajaxUpdate();
    },
    
    ajaxUpdate: function() {
        clearTimeout(App.Monitor.timeoutId);
        if (!App.paused) {
            App.ajax({
                url: App.url('ajax_update'),
                data: {ids: App.Monitor.ids.join(',')},
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
                                        var onclick = "App.Monitor.Senha.view(" + atendimento.id + ")";
                                        var item = '<li class="' + (atendimento.prioridade ? 'prioridade' : '') + '">';
                                        var title = atendimento.nomePrioridade + ' (' + atendimento.espera + ')';
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
                },
                complete: function() {
                    App.Monitor.timeoutId = setTimeout(App.Monitor.ajaxUpdate, App.updateInterval);
                }
            });
        } else {
            App.Monitor.timeoutId = setTimeout(App.Monitor.ajaxUpdate, App.updateInterval);
        }
    },
    
    Senha: {
        
        dialogView: '#dialog-view',
        dialogSearch: '#dialog-busca',
        dialogTransfere: '#dialog-transfere',
    
        /**
         * Busca informacoes do atendimento pelo id.
         */
        view: function(id) {
            App.ajax({
                url: App.url('info_senha'),
                type: 'get',
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        var dialog = $(App.Monitor.Senha.dialogView);
                        var a = response.data;
                        dialog.find('#senha_id').val(a.id);
                        dialog.find('#senha_numero').text(a.senha);
                        dialog.find('#senha_prioridade').text(a.nomePrioridade);
                        dialog.find('#senha_servico').text(a.servico);
                        dialog.find('#senha_chegada').text(App.formatDate(a.chegada));
                        dialog.find('#senha_espera').text(a.espera);
                        dialog.find('#senha_inicio').text(App.formatDate(a.inicio, '-'));
                        dialog.find('#senha_fim').text(App.formatDate(a.fim, '-'));
                        dialog.find('#senha_status').text(a.nomeStatus);
                        dialog.find('#cliente_nome').text(a.cliente.nome);
                        dialog.find('#cliente_documento').text(a.cliente.documento);
                        // so pode transferir ou cancelar se o status for 1 (senha emitida)
                        if (a.status === 1) {
                            $('#btn-transferir, #btn-cancelar').show();
                        } else {
                            $('#btn-transferir, #btn-cancelar').hide();
                        }
                        // so pode reativar se estiver cancelado ou nao compareceu
                        if (a.status === 5 || a.status === 6) {
                            $('#btn-reativar').show();
                        } else {
                            $('#btn-reativar').hide();
                        }
                        App.dialogs.modal(dialog, { width: 600 });
                    }
                }
            });
        },
        
        consulta: function() {
            App.dialogs.modal(App.Monitor.Senha.dialogSearch, { 
                width: 900,
                open: function() {
                    $('#numero_busca').val($('#buscar-senha').val());
                    App.Monitor.Senha.consultar();
                    $('#buscar-senha').val('');
                }
            });
        },
        
        consultar: function() {
            var result = $('#result_table tbody');
            result.html('');
            App.ajax({
                url: App.url('buscar'),
                data: {numero: $('#numero_busca').val()},
                success: function(response) {
                    if (response.data.total > 0) {
                        for (var i = 0; i < response.data.total; i++) {
                            var atendimento = response.data.atendimentos[i];
                            var tr = '<tr>';
                            tr += '<td><a href="javascript:void(0)" onclick="App.Monitor.Senha.view(' + atendimento.id + ')">' + atendimento.senha + '</a></td>';
                            tr += '<td>' + atendimento.servico + '</td>';
                            tr += '<td>' + App.formatDate(atendimento.chegada) + '</td>';
                            tr += '<td>' + App.formatTime(atendimento.inicio) + '</td>';
                            tr += '<td>' + App.formatTime(atendimento.fim) + '</td>';
                            tr += '<td>' + (atendimento.triagem ? atendimento.triagem : '-') + '</td>';
                            tr += '<td>' + (atendimento.usuario ? atendimento.usuario : '-') + '</td>';
                            tr += '<td>' + atendimento.nomeStatus + '</td>';
                            tr += '</tr>';
                            result.append(tr);
                        }
                    }
                }
            });
        },

        transfere: function(id, numero) {
            App.dialogs.modal(App.Monitor.Senha.dialogTransfere);
            $('#transfere_id').val(id);
            $('#transfere_numero').text(numero);
        },

        transferir: function() {
            App.ajax({
                url: App.url('transferir'),
                type: 'post',
                data: {
                    id: $('#transfere_id').val(),
                    servico: $('#transfere_servico').val(),
                    prioridade: $('#transfere_prioridade').val()
                },
                complete: function() {
                    $(App.Monitor.Senha.dialogView).modal('hide');
                    $(App.Monitor.Senha.dialogTransfere).modal('hide');
                }
            });
        },

        reativar: function(id) {
            if (window.confirm(App.Monitor.alertReativar)) {
                App.ajax({
                    url: App.url('reativar'),
                    type: 'post',
                    data: { 
                        id: id 
                    },
                    complete: function() {
                        $(App.Monitor.Senha.dialogView).modal('hide');
                        $(App.Monitor.Senha.dialogSearch).modal('hide');
                    }
                });
            }
        },

        cancelar: function(id) {
            if (window.confirm(App.Monitor.alertCancelar)) {
                App.ajax({
                    url: App.url('cancelar'),
                    type: 'post',
                    data: { 
                        id: id 
                    },
                    complete: function() {
                        $(App.Monitor.Senha.dialogView).modal('hide');
                    }
                });
            }
        }
    }
    
};