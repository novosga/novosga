/**
 * Novo SGA - Triagem
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

SGA.Triagem = {
    
    ids: [],
    imprimir: false,
    pausado: false,
    prioridades: 0,
    timeoutId: 0,
    desabilitados: [],
    
    init: function() {
        SGA.Triagem.ajaxUpdate();
        $('#dialog-busca').on('show.bs.modal', function () {
            $('#numero_busca').val('');
            $('#result_table tbody').html('');
        });
        
        this.desabilitados = JSON.parse(SGA.Triagem.Storage.get('desabilitados') || '[]');
        
        for (var i = 0; i < this.desabilitados.length; i++) {
            var item = this.desabilitados[i];
            $('.exibir-servicos .servico-' + item).prop('checked', false);
            $('#triagem-servico-' + item).hide();
        }
        
        $('.exibir-servicos input').on('change', function() {
            var elem = $(this);
            var index = SGA.Triagem.desabilitados.indexOf(elem.val());
            
            if (index !== -1) {
                SGA.Triagem.desabilitados.splice(index, 1);
            }
            if (!elem.is(':checked')) {
                SGA.Triagem.desabilitados.push(elem.val());
                $('#triagem-servico-' + elem.val()).hide();
            } else {
                $('#triagem-servico-' + elem.val()).show();
            }
            
            SGA.Triagem.Storage.set('desabilitados', JSON.stringify(SGA.Triagem.desabilitados));
        });
    },
    
    servicoInfo: function(servico) {
        SGA.ajax({
            type: 'get',
            url: SGA.url('servico_info'),
            data: {
                id: servico
            },
            success: function(response) {
                var dialog = $("#dialog-servico");
                dialog.find('p.ultima-senha span').text(response.data.senha);
                var btnPrint = dialog.find('p.ultima-senha a');
                btnPrint.off();
                if (response.data.senhaId) {
                    btnPrint.show().on('click', function() {
                        SGA.Triagem.Impressao.loadIframe({ id: response.data.senhaId });
                        return false;
                    });
                } else {
                    btnPrint.hide();
                }
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
                SGA.dialogs.modal(dialog);
            }
        });
    },
    
    ajaxUpdate: function() {
        clearTimeout(SGA.Triagem.timeoutId);
        if (!SGA.paused) {
            SGA.ajax({
                url: SGA.url('ajax_update'),
                data: {
                    ids: SGA.Triagem.ids.join(',')
                },
                success: function(response) {
                    $('.fila .total').text('-');
                    if (response.success) {
                        if (response.data.servicos) {
                            for (var i in response.data.servicos) {
                                var qtd = response.data.servicos[i];
                                $('#total-aguardando-' + i).text(qtd.fila);
                                $('#total-senhas-' + i).text(qtd.total);
                            }
                        }
                        if (response.data.ultima) {
                            var elem = $('#infobar .ultima-senha .label');
                            elem.html('<span class="glyphicon glyphicon-print"></span> ' + response.data.ultima.senha);
                            if (response.data.ultima.prioridade) {
                                elem.removeClass('label-default').addClass('label-danger');
                            } else {
                                elem.removeClass('label-danger').addClass('label-default');
                            }
                        }
                    }
                },
                complete: function() {
                    SGA.Triagem.timeoutId = setTimeout(SGA.Triagem.ajaxUpdate, SGA.updateInterval);
                }
            });
        } else {
            SGA.Triagem.timeoutId = setTimeout(SGA.Triagem.ajaxUpdate, SGA.updateInterval);
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
            return SGA.url('imprimir') + "?id=" + atendimento.id;
        },
        
        loadIframe: function(atendimento) {
            var iframe = document.getElementById(SGA.Triagem.Impressao.iframe);
            if (iframe) {
                iframe.src = SGA.Triagem.Impressao.url(atendimento);
            }
        }
        
    },
            
    distribuiSenha: function(servico, prioridade, complete) {
        var cliente = {
            nome: $('#cli_nome').val(),
            doc: $('#cli_doc').val()
        };
        $('#cli_nome, #cli_doc').val('');
        if (!SGA.Triagem.pausado) {
            // evitando de gerar várias senhas com múltiplos cliques
            SGA.Triagem.pausado = true;
            SGA.ajax({
                url: SGA.url('distribui_senha'),
                type: 'post',
                data: {
                    servico: servico, 
                    prioridade: prioridade,
                    cli_nome: cliente.nome || '',
                    cli_doc: cliente.doc || ''
                },
                success: function(response) {
                    SGA.Triagem.Impressao.imprimir(response.data);
                    SGA.Triagem.ajaxUpdate();
                    if (typeof(complete) === 'function') {
                        complete(response);
                    }
                    var dialog = $("#dialog-senha");
                    SGA.dialogs.modal(dialog, { 
                        width: 450, 
                        buttons: {},
                        open: function() {
                            var a = response.data;
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
                },
                complete: function() {
                    SGA.Triagem.pausado = false;
                }
            });
        }
    },

    senhaNormal: function(btn) {
        btn = $(btn);
        SGA.Triagem.distribuiSenha(btn.data('id'), 1);
    },

    senhaPrioridade: function(btn, complete) {
        btn = $(btn);
        SGA.Triagem.distribuiSenha(btn.data('id'), $('input:radio[name=prioridade]:checked').val(), complete);
    },

    prioridade: function(btn) {
        if (SGA.Triagem.prioridades.length === 1) {
            // se so tiver uma prioridade, emite a senha direto
            SGA.Triagem.distribuiSenha($(btn).data('id'), SGA.Triagem.prioridades[0]);
        } else {
            var dialog = $("#dialog-prioridade");
            SGA.dialogs.modal(dialog, { 
                create: function() {
                    $('input:radio[name=prioridade]').on('click', function() {
                        dialog.find("button").prop('disabled', false);
                    });
                    dialog.find("button").data('id', $(btn).data('id'));
                },
                open: function() {
                    $('input:radio[name=prioridade]').prop('checked', false);
                    dialog.find("button").prop('disabled', true);
                }
            });
        }
    },

    consultar: function() {
        SGA.ajax({
            url: SGA.url('consulta_senha'),
            data: {numero: $('#numero_busca').val()},
            success: function(response) {
                var result = $('#result_table tbody');
                result.html('');
                if (response.data.total > 0) {
                    for (var i = 0; i < response.data.total; i++) {
                        var atendimento = response.data.atendimentos[i];
                        var btnPrint = '<a href="#" class="glyphicon glyphicon-print" onclick="SGA.Triagem.Impressao.loadIframe({ id: ' + atendimento.id +' }); return false;"></a>';
                        var tr = '<tr>';
                        tr += '<td>' + atendimento.senha + ' ' + btnPrint + '</td>';
                        tr += '<td>' + atendimento.servico + '</td>';
                        tr += '<td>' + SGA.formatDate(atendimento.chegada) + '</td>';
                        tr += '<td>' + SGA.formatTime(atendimento.inicio) + '</td>';
                        tr += '<td>' + SGA.formatTime(atendimento.fim) + '</td>';
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
    
    Storage: {
        
        prefix: 'novosga.triagem.',

        set: function(name, value) {
            name = this.prefix + name;
            if (localStorage) {
                localStorage.setItem(name, value);
            } else {
                // cookie
                var expires = "";
                document.cookie = name + "=" + value + expires + "; path=/";
            }
        },
                
        get: function(name) {
            name = this.prefix + name;
            if (localStorage) {
                return localStorage.getItem(name);
            } else {
                // cookie
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) === ' ') {
                        c = c.substring(1,c.length);
                    }
                    if (c.indexOf(nameEQ) === 0) {
                        return c.substring(nameEQ.length, c.length);
                    }
                }
            }
            return null;
        }

    },
    
};
