/**
 * Novo SGA - Admin
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Admin = {
    
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
    
    painelInfo: function(unidade, host) {
        SGA.ajax({
            url: SGA.url('painel_info'),
            data: {unidade: unidade, host: host},
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