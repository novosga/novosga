/**
 * Novo SGA - Unidade
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Unidade = {
        
    Servicos: {
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
                    btn.button("enable")
                }
            });
        },
        enable: function(btn) {
            btn = $(btn);
            btn.button("disable");
            SGA.Unidade.Servicos.request('habilita_servico', btn, function() {
                $('#sigla-' + btn.data('id')).prop('disabled', false).focus();
                $('#btn-disable-' + btn.data('id')).button("enable")
            });
        },
        
        disable: function(btn) {
            btn = $(btn);
            btn.button("disable");
            SGA.Unidade.Servicos.request('desabilita_servico', btn, function() {
                $('#sigla-' + btn.data('id')).prop('disabled', true);
                $('#btn-enable-' + btn.data('id')).button("enable")
            });
        },
        
        updateSigla: function(input) {
            input = $(input);
            SGA.ajax({
                url: SGA.url('update_sigla'),
                data: {id: input.data('id'), sigla: input.val()},
                type: 'post'
            });
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
                for (var i = 0; i < painel.servicos.length; i++) {
                    list.append('<li>' + painel.servicos[i] + '</li>')
                }
                list = $('#painel_senhas');
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