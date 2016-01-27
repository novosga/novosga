/**
 * Novo SGA - Atendimento
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

App.Atendimento = {
    
    filaVazia: '',
    remover: '',
    marcarNaoCompareceu: '',
    marcarErroTriagem: '',
    nenhumServicoSelecionado: '',
    defaultTitle: '',
    timeoutId: 0,
    tiposAtendimento: {},
    
    init: function(status) {
        App.Atendimento.ajaxUpdate();
        App.Atendimento.updateControls(status);
        $('#dialog-busca').on('show.bs.modal', function () {
            $('#numero_busca').val('');
            $('#result_table tbody').html('');
        });
        App.Atendimento.defaultTitle = document.title;
        if (!App.Notification.allowed()) {
            $('#notification').show();
        }
    },
    
    ajaxUpdate: function() {
        clearTimeout(App.Atendimento.timeoutId);
        if (!App.paused) {
            App.ajax({
                url: App.url('ajax_update'),
                success: function(response) {
                    response.data = response.data || {};
                    var atendimentos = response.data.atendimentos || [],
                            usuario = response.data.usuario || {};
                    var list = $("#fila ul");
                    // habilitando botao chamar
                    if (atendimentos.length > 0) {
                        $('#chamar .chamar').prop('disabled', false);
                        // se a fila estava vazia e chegou um novo atendimento, entao toca o som
                        if (list.find('li.empty').length > 0) {
                            document.getElementById("alert").play();
                            App.Notification.show('Atendimento', 'Novo atendimento na fila');
                        }
                    }
                    list.text('');
                    if (atendimentos.length > 0) {
                        document.body.focus();
                        for (var i = 0; i < atendimentos.length; i++) {
                            var atendimento = atendimentos[i];
                            var cssClass = atendimento.prioridade ? 'prioridade' : '';
                            if (i == 0) {
                                cssClass += ' proximo';
                            }
                            var onclick = 'App.Atendimento.infoSenha(' + atendimento.id + ')';
                            var title = atendimento.servico + ' (' + atendimento.espera + ')';
                            var item = '<li><a class="' + cssClass + '" href="javascript:void(0)" onclick="' + onclick + '" title="' + title + '">' + atendimento.senha + '</a></li>';
                            list.append(item);
                        }
                        document.title = "(" + atendimentos.length + ") " + App.Atendimento.defaultTitle;
                    } else {
                        $('#chamar .chamar').prop('disabled', true);
                        list.append('<li class="empty">' + App.Atendimento.filaVazia + '</li>');
                        document.title = App.Atendimento.defaultTitle;
                    }
                    if (usuario.numeroLocal) {
                        $('span.config-numero-local').text(usuario.numeroLocal);
                        $('.config-numero-local:input').val(usuario.numeroLocal);
                    }
                    if (usuario.tipoAtendimento) {
                        $('span.config-tipo-atendimento')
                                .removeClass('tipo-1 tipo-2 tipo-3')
                                .addClass('tipo-' + usuario.tipoAtendimento)
                                .text(App.Atendimento.tiposAtendimento[usuario.tipoAtendimento]);
                        $('.config-tipo-atendimento:input').val(usuario.tipoAtendimento);
                        ;
                    }
                },
                complete: function() {
                    App.Atendimento.timeoutId = setTimeout(App.Atendimento.ajaxUpdate, App.updateInterval);
                }
            });
        } else {
            App.Atendimento.timeoutId = setTimeout(App.Atendimento.ajaxUpdate, App.updateInterval);
        }
    },
    
    updateControls: function(status, atendimento) {
        $('#controls .control').hide();
        switch (status) {
            case 1: // nenhum atendimento, chamar
                $('#chamar').show();
                $('#redirecionar_servico').val(0);
                $('#encerrar-redirecionar').prop('checked', false);
                break;
            case 2: // senha chamada
                if (atendimento) {
                    var info = $('.senha .info');
                    info.removeClass('prioridade');
                    if (atendimento.prioridade) {
                        info.addClass('prioridade');
                    }
                    info.find('.numero .atend-value').text(atendimento.senha);
                    info.find('.nome-prioridade .atend-value').text(atendimento.nomePrioridade);
                    info.find('.servico .atend-value').text(atendimento.servico);
                    info.find('.nome .atend-value').text(atendimento.cliente.nome || '-');
                }
                $('#iniciar').show();
                break;
            case 3: // atendimento iniciado
                $('#encerrar').show();
                var btnEncerrar = $('#encerrar .encerrar');
                btnEncerrar.prop('disabled', true);
                // habilita o botao encerrar depois de X segundos (issue #249)
                setTimeout(function() {
                    btnEncerrar.prop('disabled', false);
                }, 3000);
                break;
            case 4: // atendimento encerrado (faltando codificar)
                $("#codificar").show();
                $("#macro-servicos li").show();
                $("#servicos-realizados").html('');
                break;
        }
    },
    
    control: function(prop) {
        prop = prop || {};
        $(prop.button).prop('disabled', true);
        App.ajax({
            url: App.url(prop.action),
            type: 'post',
            data: prop.data || {},
            success: function(response) {
                if (prop.success) {
                    prop.success(response);
                }
            },
            complete: function() {
                var delay = prop.enableDelay || 0;
                window.setTimeout(function() {
                    $(prop.button).prop('disabled', false);
                }, delay);
            }
        });
    },
    
    chamar: function(btn) {
        App.Atendimento.control({
            button: btn,
            enableDelay: 5000,
            action: 'chamar', 
            success: function(response) {
                // remove o proximo da lista se for o mesmo do atendimento
                var proximo = $("#fila ul li:first");
                if (response.data.senha == proximo.text()) {
                    proximo.remove();
                    if ($("#fila ul li").length == 0) {
                        // fila vazia
                        $("#fila ul").append('<li class="empty">' + App.Atendimento.filaVazia + '</li>')
                    } else {
                        // novo proximo
                        $("#fila ul li:first a").addClass('proximo'); 
                    }
                }
                App.Atendimento.updateControls(2, response.data);
            }
        });
    },
    
    chamar_novamente: function(btn) {
        App.Atendimento.control({
            button: btn,
            enableDelay: 5000,
            action: 'chamar'
        });
    },
    
    iniciar: function(btn) {
        App.Atendimento.control({
            button: btn,
            action: 'iniciar', 
            success: function(response) {
                App.Atendimento.updateControls(3, response.data)
            }
        });
    },
    
    nao_compareceu: function(btn) {
        if (window.confirm(App.Atendimento.marcarNaoCompareceu)) {
            App.Atendimento.control({
                button: btn,
                action: 'nao_compareceu', 
                success: function(response) {
                    App.Atendimento.updateControls(1, response.data)
                }
            });
        }
    },
    
    encerrar: function(btn) {
        App.Atendimento.control({
            button: btn,
            action: 'encerrar', 
            success: function(response) {
                App.Atendimento.updateControls(4, response.data)
            }
        });
    },
    
    encerrar_voltar: function() {
        $("#encerrar").show();
        $("#codificar").hide();
    },
    
    codificar: function(btn, isRedirect) {
        var servicos = [];
        $('#servicos-realizados input.servicos').each(function(i, e) {
            servicos.push($(e).val());
        });
        if (servicos.length == 0) {
            alert(App.Atendimento.nenhumServicoSelecionado);
            return;
        }
        var data = {
            servicos: servicos.join(',')
        };
        // se foi submetido via modal de redirecionamento
        if (isRedirect) {
            var servico = $('#redirecionar_servico').val();
            if (isNaN(servico) || servico <= 0) {
                alert(App.Atendimento.nenhumServicoSelecionado);
                return;
            }
            data.redirecionar = true;
            data.novoServico = servico;
            // definindo o botao da dialog para ser desabilitado
            btn = $('#dialog-redirecionar').parent().find(':button');
        } else {
            // verifica se checkbox redirecionar esta marcado, para abrir a modal
            var redirecionar = $('#encerrar-redirecionar').is(':checked');
            if (redirecionar) {
                var modal = App.dialogs.modal('#dialog-redirecionar');
                modal.find('button').hide();
                modal.find('.btn-codificar').show();
                return;
            }
        }
        App.Atendimento.control({
            button: btn,
            action: 'codificar', 
            data: data,
            success: function() {
                App.Atendimento.updateControls(1);
                if (isRedirect) {
                    $('#dialog-redirecionar').modal('hide');
                }
            }
        });
    },
    
    infoSenha: function(id) {
        App.ajax({
            url: App.url('info_senha'),
            data: {
                id: id
            },
            success: function(response) {
                if (response.success) {
                    var a = response.data;
                    var dialog = $('#dialog-senha');
                    dialog.find('.numero').text(a.senha);
                    dialog.find('.nome-prioridade').text(a.nomePrioridade);
                    dialog.find('.servico').text(a.servico);
                    dialog.find('.chegada').text(App.formatDate(a.chegada));
                    dialog.find('.espera').text(a.espera);
                    App.dialogs.modal(dialog);
                }
            }
        });
    },
    
    erro_triagem: function() {
        var modal = App.dialogs.modal('#dialog-redirecionar');
        modal.find('button').hide();
        modal.find('.btn-redirecionar').show();
    },
    
    redirecionar: function(btn) {
        var servico = $('#redirecionar_servico').val();
        if (servico > 0 && window.confirm(App.Atendimento.marcarErroTriagem)) {
            App.Atendimento.control({
                button: btn,
                action: 'redirecionar', 
                data: {servico: servico},
                success: function() {
                    App.Atendimento.updateControls(1);
                    $('#dialog-redirecionar').modal('hide');
                }
            });
        }
    },
    
    addServico: function(item) {
        item = $(item);
        $("#servicos-realizados").append('<li><a href="javascript:void(0)" onclick="App.Atendimento.delServico(this)" title="' + App.Atendimento.remover + '"><input type="hidden" class="servicos" value="' + item.data('id') + '" />' + item.text() + '</a></li>');
        $(item).parent().hide();
    },
    
    delServico: function(item) {
        item = $(item); 
        $('#servico-' + item.find('input').val()).show();
        item.parent().remove();
    },

    consultar: function() {
        App.ajax({
            url: App.url('consulta_senha'),
            data: {
                numero: $('#numero_busca').val()
            },
            success: function(response) {
                var result = $('#result_table tbody');
                result.html('');
                if (response.data.total > 0) {
                    for (var i = 0; i < response.data.total; i++) {
                        var atendimento = response.data.atendimentos[i];
                        var tr = '<tr>';
                        tr += '<td>' + atendimento.senha + '</td>';
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
    }
    
};