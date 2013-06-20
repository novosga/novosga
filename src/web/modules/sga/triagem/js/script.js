/**
 * Novo SGA - Triagem
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Triagem = {
    
    ids: [],
    imprimir: false,
    pausado: false,
    
    init: function() {
        setInterval(SGA.Triagem.ajaxUpdate, SGA.updateInterval);
    },
    
    servicoInfo: function(servico, title) {
        SGA.ajax({
            type: 'post',
            url: SGA.url('servico_info'),
            data: {id: servico},
            success: function(response) {
                var dialog = $("#dialog-servico");
                dialog.find('p.nome').text(response.data.nome);
                dialog.find('p.descricao').text(response.data.descricao);
                var subservicos = dialog.find('ul.subservicos.notempty');
                if (response.data.subservicos && response.data.subservicos.length > 0) {
                    subservicos.html('');
                    for (var i = 0; i < response.data.subservicos.length; i++) {
                        subservicos.append('<li>' + response.data.subservicos[i] + '</li>');
                    }
                    subservicos.show();
                    dialog.find('ul.subservicos.empty').hide();
                } else {
                    subservicos.hide();
                    dialog.find('ul.subservicos.empty').show();
                }
                SGA.dialogs.modal(dialog, { title: title, width: 650 });
            }
        });
    },
    
    ajaxUpdate: function() {
        if (!SGA.paused) {
            SGA.ajax({
                url: SGA.url('ajax_update'),
                data: {ids: SGA.Triagem.ids.join(',')},
                success: function(response) {
                    $('.fila .total').text('-');
                    if (response.success) {
                        for (var i in response.data) {
                            var qtd = response.data[i];
                            $('#total-aguardando-' + i).text(qtd.fila);
                            $('#total-senhas-' + i).text(qtd.total);
                        }
                    }
                }
            });
        }
    },
    
    Impressao: {
        
        iframe: 'frame-impressao',
        
        imprimir: function(atendimento) {
            if (SGA.Triagem.imprimir) {
                SGA.Triagem.Impressao.loadIframe(atendimento);
            }
        },
        
        url: function(atendimento) {
            return SGA.url('imprimir') + "&id=" + atendimento.id;
        },
        
        loadIframe: function(atendimento) {
            var iframe = document.getElementById(SGA.Triagem.Impressao.iframe);
            if (iframe) {
                iframe.src = SGA.Triagem.Impressao.url(atendimento);
            }
        }
        
    },
    
    distribuiSenha: function(servico, prioridade, cliente, success) {
        if (!SGA.Triagem.pausado) {
            // evitando de gerar várias senhas com múltiplos cliques
            SGA.Triagem.pausado = true;
            SGA.ajax({
                url: SGA.url('distribui_senha'),
                data: {
                    servico: servico, 
                    prioridade: prioridade,
                    cli_nome: cliente.nome || '',
                    cli_doc: cliente.doc || ''
                },
                type: 'post',
                success: function(response) {
                    SGA.Triagem.Impressao.imprimir(response.data);
                    SGA.Triagem.ajaxUpdate();
                    if (typeof(success) === 'function') {
                        success(response);
                    }
                    SGA.Triagem.pausado = false;
                }
            });
            $('#cli_nome, #cli_doc').val('');
        }
    },
            
    Web: {
        
        distribuiSenha: function(servico, prioridade, complete) {
            var cliente = {
                nome: $('#cli_nome').val(),
                doc: $('#cli_doc').val()
            };
            $('#cli_nome, #cli_doc').val('');
            SGA.Triagem.distribuiSenha(servico, prioridade, cliente, function(response) {
                if (typeof(complete) === 'function') {
                    complete(response);
                }
                var dialog = $("#dialog-senha");
                SGA.dialogs.modal(dialog, { 
                    width: 450, 
                    buttons: {},
                    open: function(event, ui) {
                        var a = response.data.atendimento;
                        dialog.find('.numero').text(a.senha);
                        dialog.find('.servico').text(a.servico);
                        dialog.find('.nome-prioridade').text(a.nomePrioridade);
                        if (a.prioridade) {
                            dialog.find('>div').addClass('prioridade');
                        } else {
                            dialog.find('>div').removeClass('prioridade');
                        }
                    }
                });
            });
        },
        
        senhaNormal: function(btn) {
            btn = $(btn);
            SGA.Triagem.Web.distribuiSenha(btn.data('id'), 1);
        },

        senhaPrioridade: function(btn, complete) {
            btn = $(btn);
            SGA.Triagem.Web.distribuiSenha(btn.data('id'), $('input:radio[name=prioridade]:checked').val(), complete);
        },
    
        prioridade: function(btn, btnLabel) {
            var btns = {};
            var dialog = $("#dialog-prioridade");
            btns[btnLabel] = function() {
                SGA.Triagem.Web.senhaPrioridade(btn, function() {
                    dialog.dialog("close");
                });
            }
            SGA.dialogs.modal(dialog, { 
                width: 450, 
                buttons: btns,
                create: function(event, ui) {
                    $('input:radio[name=prioridade]').on('click', function() {
                        dialog.parent().find("button").button('enable');
                    });
                },
                open: function(event, ui) {
                    $('input:radio[name=prioridade]').prop('checked', false);
                    dialog.parent().find("button").button('disable');
                }
            });
        }

    },
    
    Touchscreen: {
        
        servico: 0,
        prioridade: 0,
        
        inicio: function(id) {
            SGA.Triagem.Touchscreen.servico = 0;
            SGA.Triagem.Touchscreen.prioriade = 0;
            $('.page').hide();
            $('#page-servicos').show();
        },
        
        chooseServico: function(id) {
            SGA.Triagem.Touchscreen.servico = id;
            SGA.Triagem.Touchscreen.tipoAtendimento();
        },
        
        tipoAtendimento: function() {
            $('.page').hide();
            $('#page-tipo').show();
        },
        
        chooseAtendimentoNormal: function() {
            SGA.Triagem.Touchscreen.prioridade = 1;
            SGA.Triagem.Touchscreen.fim();
        },
        
        chooseAtendimentoPrioridade: function() {
            $('.page').hide();
            $('#page-prioridades').show();
        },
        
        choosePrioridade: function(id) {
            SGA.Triagem.Touchscreen.prioridade = id;
            SGA.Triagem.Touchscreen.fim();
        },
        
        fim: function() {
            if (!SGA.Triagem.Touchscreen.servico || SGA.Triagem.Touchscreen.servico <= 0) {
                SGA.Triagem.Touchscreen.inicio();
            }
            if (!SGA.Triagem.Touchscreen.prioridade || SGA.Triagem.Touchscreen.prioridade <= 0) {
                SGA.Triagem.Touchscreen.tipoAtendimento();
            }
            SGA.Triagem.distribuiSenha(
                SGA.Triagem.Touchscreen.servico, 
                SGA.Triagem.Touchscreen.prioridade, 
                { nome: 'Touchscreen' },
                function(response) {
                    var atendimento = response.data.atendimento;
                    $('.page').hide();
                    $('#senha-numero').text(atendimento.senha);
                    $('#senha-servico').text(atendimento.servico);
                    $('#senha-tipo').text(atendimento.nomePrioridade);
                    $('#page-fim').show();
                    window.setTimeout(SGA.Triagem.Touchscreen.inicio, 3000);
                }
            );
        }
        
    }
    
};
