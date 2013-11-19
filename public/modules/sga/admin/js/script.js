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
    
    checkVersion: function() {
        var self = this;
        this.intervalId = self.intervalId || 0;
        clearInterval(self.intervalId);
        var icon = $('#btn-checkversion').prop('disabled', true).find('span');
        icon.css('visibility', 'hidden');
        self.intervalId = setInterval(function() {
            icon.css('visibility', icon.css('visibility') === 'visible' ? 'hidden' : 'visible');
        }, 200);
        $.ajax({
            url: 'https://api.github.com/repos/novosga/novosga/tags',
            success: function(response) {
                clearInterval(self.intervalId);
                $('#btn-checkversion').hide();
                var latest = response[0];
                if (SGA.version !== latest.name.replace('v', '')) {
                    $('#btn-downloader')
                            .show()
                            .prop('href', latest.zipball_url)
                            .find('.version')
                            .text(latest.name);
                } else {
                    $('#update-alert').show();
                }
            }
        });
        return false;
    }
    
};