/**
 * Novo SGA - Triagem
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Triagem = {
    
    ids: [],
    paused: false,
    imprimir: false,
    ajaxInterval: 3000,
    
    servicoInfo: function(servico, title) {
        $.ajax({
            type: 'post',
            url: SGA.url('servico_info'),
            data: {id: servico},
            success: function(response) {
                var dialog = $("#dialog-servico");
                dialog.find('p.descricao').text(response.descricao);
                var subservicos = dialog.find('ul.subservicos.notempty');
                if (response.subservicos && response.subservicos.length > 0) {
                    subservicos.html('');
                    for (var i = 0; i < response.subservicos.length; i++) {
                        subservicos.append('<li>' + response.subservicos[i] + '</li>');
                    }
                    subservicos.show();
                    dialog.find('ul.subservicos.empty').hide();
                } else {
                    subservicos.hide();
                    dialog.find('ul.subservicos.empty').show();
                }
                dialog.dialog({
                    modal: true,
                    title: title,
                    width: 650
                });
            }
        });
    },
    
    prioridade: function(btn, btnLabel) {
        var btns = {};
        var dialog = $("#dialog-prioridade");
        btns[btnLabel] = function() {
            SGA.Triagem.senhaPrioridade(btn, function() {
                dialog.dialog("close");
            });
        }
        dialog.dialog({
            modal: true,
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
    },
    
    ajaxUpdate: function() {
        if (!SGA.Triagem.paused) {
            $.ajax({
                url: SGA.url('ajax_update'),
                data: {ids: SGA.Triagem.ids.join(',')},
                success: function(response) {
                    for (var i in response) {
                        var qtd = response[i];
                        $('#total-aguardando-' + i).text(qtd.fila);
                        $('#total-senhas-' + i).text(qtd.total);
                    }
                }
            });
        }
    },
    
    distribuiSenha: function(servico, prioridade, complete) {
        $.ajax({
            url: SGA.url('distribui_senha'),
            data: {
                servico: servico, 
                prioridade: prioridade,
                cli_nome: $('#cli_nome').val(),
                cli_doc: $('#cli_doc').val()
            },
            type: 'post',
            success: function(response) {
                if (response.success) {
                    if (SGA.Triagem.imprimir) {
                        window.open(SGA.url('imprimir') + "&id=" + response.id);
                    }
                    // TODO: atualizar pelo response, ao inves de fazer outro request
                    SGA.Triagem.ajaxUpdate();
                } else {
                    alert(response.message);
                }
            },
            complete: complete
        });
        $('#cli_nome, #cli_doc').val('');
    },
    
    senhaNormal: function(btn) {
        btn = $(btn);
        SGA.Triagem.distribuiSenha(btn.data('id'), 1, function() {});
    },
    
    senhaPrioridade: function(btn, complete) {
        btn = $(btn);
        SGA.Triagem.distribuiSenha(btn.data('id'), $('input:radio[name=prioridade]:checked').val(), complete);
    }
    
}
