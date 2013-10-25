/**
 * Novo SGA - Admin
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

SGA.Admin = {
    
    Auth: {
        init: function() {
            $('#auth_type').on('change', function() {
                var value = $(this).val();
                $('.auth-config, #auth-' + value + ' span.error').hide();
                $('#auth-' + value).show();
            });
        },
        values: function() {
            var data = {type: $('#auth_type').val()};
            $('#auth-' + data.type + ' input').each(function(i,e) {
                var input = $(e);
                data[input.prop('name')] = input.val();
            });
            return data;
        },
        save: function() {
            $('#auth_message').hide();
            var data = SGA.Admin.Auth.values();
            if (SGA.Form.checkRequireds('#auth-' + data.type)) {
                SGA.ajax({
                    url: SGA.url('auth_save'),
                    data: data,
                    type: 'post',
                    success: function(response) {
                        $('#auth_message').show();
                    }
                });
            }
            return false;
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
    
    changeNumeracao: function() {
        SGA.ajax({
            url: SGA.url('change_numeracao'),
            data: {tipo: $('#numeracao').val()},
            success: function(response) {
            }
        });
    },
    
};