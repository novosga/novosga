/**
 * Novo SGA - Unidade
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Unidade = {
        
    Servicos: {
        init: function() {
            $('#servicos td.nome input').each(function(i,v) {
                var input = $(v);
                input.width(input.parent().width() - 60);
            });
        },
        request: function(method, btn, complete) {
            btn = $(btn);
            SGA.ajax({
                url: SGA.url(method),
                data: {id: btn.data('id')},
                type: 'post',
                success: function(response) {
                    complete(response.success);
                },
                error: function() {
                    btn.prop("disabled", false)
                }
            });
        },
        enable: function(btn) {
            btn = $(btn);
            btn.prop("disabled", true);
            SGA.Unidade.Servicos.request('habilita_servico', btn, function() {
                $('.servico-' + btn.data('id')).prop('disabled', false).focus();
                $('#btn-disable-' + btn.data('id')).prop("disabled", false);
            });
        },
        
        disable: function(btn) {
            btn = $(btn);
            btn.prop("disabled", true);
            SGA.Unidade.Servicos.request('desabilita_servico', btn, function() {
                $('.servico-' + btn.data('id')).prop('disabled', true);
                $('#btn-enable-' + btn.data('id')).prop("disabled", false);
            });
        },
        
        updateSigla: function(input) {
            input = $(input);
            if (input.val().length > 0) {
                SGA.ajax({
                    url: SGA.url('update_sigla'),
                    data: {id: input.data('id'), sigla: input.val()},
                    type: 'post'
                });
            }
        },
        
        updateNome: function(input) {
            input = $(input);
            if (input.val().length > 0) {
                SGA.ajax({
                    url: SGA.url('update_nome'),
                    data: {id: input.data('id'), nome: input.val()},
                    type: 'post'
                });
            }
        },
        
        reverteNome: function(id) {
            if (id > 0) {
                SGA.ajax({
                    url: SGA.url('reverte_nome'),
                    data: {id: id},
                    type: 'post',
                    success: function(response) {
                        $('#nome-' + id).val(response.data.nome);
                    }
                });
            }
        }
    },
    
    reiniciarSenhas: function(alert) {
        if (confirm(alert)) {
            SGA.ajax({
                url: SGA.url('acumular_atendimentos'),
                success: function(response) {
                    SGA.dialogs.modal("#dialog-reiniciar");
                }
            });
        }
        return false;
    },
    
    painelInfo: function(host) {
        SGA.ajax({
            url: SGA.url('painel_info'),
            data: {host: host},
            success: function(response) {
                var painel = response.data;
                $('#painel_ip').text(painel.ip);
                $('#painel_unidade').text(painel.unidade);
                var list = $('#painel_servicos');
                list.html('');
                for (var i = 0; i < painel.servicos.length; i++) {
                    list.append('<li>' + painel.servicos[i] + '</li>')
                }
                list = $('#painel_senhas');
                list.html('');
                for (var i = 0; i < painel.senhas.length; i++) {
                    list.append('<li>' + painel.senhas[i] + '</li>')
                }
                SGA.dialogs.modal('#dialog-painel', {
                    width: 500
                });
            }
        });
    }
    
};