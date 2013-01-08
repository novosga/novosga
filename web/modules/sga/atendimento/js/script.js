/**
 * Novo SGA - Atendimento
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Atendimento = {
    
    filaVazia: '',
    remover: '',
    marcarNaoCompareceu: '',
    marcarErroTriagem: '',
    nenhumServicoSelecionado: '',
    
    init: function(status) {
        setInterval(SGA.Atendimento.ajaxUpdate, SGA.updateInterval);
        SGA.Atendimento.ajaxUpdate();
        SGA.Atendimento.updateControls(status);
    },
    
    ajaxUpdate: function() {
        if (!SGA.paused) {
            SGA.ajax({
                url: SGA.url('get_fila'),
                success: function(response) {
                    if (response.success) {
                        var list = $("#fila ul");
                        // se a fila estava vazia e chegou um novo atendimento, entao toca o som
                        if (response.data.length > 0 && list.find('li.empty').length > 0) {
                            $('#chamar .chamar').button('enable');
                            document.getElementById("audio-new").play();
                        }
                        list.text('');
                        if (response.data.length > 0) {
                            for (var i = 0; i < response.data.length; i++) {
                                var atendimento = response.data[i];
                                var cssClass = atendimento.prioridade ? 'prioridade' : '';
                                if (i == 0) {
                                    cssClass += ' proximo';
                                }
                                var item = '<li class="' + cssClass + '"><abbr title="' + atendimento.servico + '">' + atendimento.senha + '</abbr></li>';
                                list.append(item);
                            }
                        } else {
                            $('#chamar .chamar').button('disable');
                            list.append('<li class="empty">' + SGA.Atendimento.filaVazia + '</li>')
                        }
                    }
                }
            });
        }
    },
    
    updateGuiche: function(btnLabel) {
        var btns = {};
        btns[btnLabel] = function() {
            $('#guiche_form').submit();
        }
        SGA.dialogs.modal('#dialog-guiche', { width: 200, buttons: btns });
    },
    
    updateControls: function(status, atendimento) {
        $('#controls button').button();
        $('#controls .control').hide();
        switch (status) {
        case 1: // nenhum atendimento, chamar
            $('#chamar').show();
            break;
        case 2: // senha chamada
            if (atendimento) {
                var info = $('.senha .info');
                info.removeClass('prioridade');
                if (atendimento.prioridade) {
                    info.addClass('prioridade');
                }
                info.find('.numero .value').text(atendimento.senha);
                info.find('.nome-prioridade .value').text(atendimento.nomePrioridade);
                info.find('.servico .value').text(atendimento.servico);
                info.find('.nome .value').text(atendimento.cliente.nome);
            }
            $('#iniciar').show();
            break;
        case 3: // atendimento iniciado
            $('#encerrar').show();
            break;
        }
    },
    
    control: function(prop) {
        prop = prop || {};
        SGA.ajax({
            url: SGA.url(prop.action),
            data: prop.data || {},
            success: function(response) {
                if (response.success) {
                    prop.success(response);
                }
            }
        });
    },
    
    chamar: function() {
        SGA.Atendimento.control({
            action: 'chamar', 
            success: function(response) {
                // remove o proximo da lista se for o mesmo do atendimento
                var proximo = $("#fila ul li:first");
                if (response.data.senha == proximo.text()) {
                    proximo.remove();
                    if ($("#fila ul li").length == 0) {
                        // fila vazia
                        $("#fila ul").append('<li class="empty">' + SGA.Atendimento.filaVazia + '</li>')
                    } else {
                        // novo proximo
                        $("#fila ul li:first").addClass('proximo'); 
                    }
                }
                SGA.Atendimento.updateControls(2, response.data);
            }
        });
    },
    
    iniciar: function() {
        SGA.Atendimento.control({
            action: 'iniciar', 
            success: function(response) {
                SGA.Atendimento.updateControls(3, response.data)
            }
        });
    },
    
    nao_compareceu: function() {
        if (window.confirm(SGA.Atendimento.marcarNaoCompareceu)) {
            SGA.Atendimento.control({
                action: 'nao_compareceu', 
                success: function(response) {
                    SGA.Atendimento.updateControls(1, response.data)
                }
            });
        }
    },
    
    encerrar: function() {
        $("#encerrar").hide();
        $("#encerrar-servicos").show();
        $("#macro-servicos li").show();
        $("#servicos-realizados").html('');
    },
    
    encerrar_voltar: function() {
        $("#encerrar").show();
        $("#encerrar-servicos").hide();
    },
    
    encerrar_servicos: function() {
        var servicos = [];
        $('#servicos-realizados input.servicos').each(function(i, e) {
            servicos.push($(e).val());
        });
        if (servicos.length == 0) {
            alert(SGA.Atendimento.nenhumServicoSelecionado);
            return false;
        }
        SGA.Atendimento.control({
            action: 'encerrar', 
            data: {servicos: servicos.join(',')},
            success: function() {
                SGA.Atendimento.updateControls(1)
            }
        });
    },
    
    erro_triagem: function() {
        if (window.confirm(SGA.Atendimento.marcarErroTriagem)) {
            SGA.Atendimento.control({
                action: 'erro_triagem', 
                success: function() {
                    SGA.Atendimento.updateControls(1)
                }
            });
        }
    },
    
    addServico: function(item) {
        item = $(item);
        $("#servicos-realizados").append('<li><a href="javascript:void(0)" onclick="SGA.Atendimento.delServico(this)" title="' + SGA.Atendimento.remover + '"><input type="hidden" class="servicos" value="' + item.data('id') + '" />' + item.text() + '</a></li>');
        $(item).parent().hide();
    },
    
    delServico: function(item) {
        item = $(item); 
        $('#servico-' + item.find('input').val()).show();
        item.parent().remove();
    }
    
};