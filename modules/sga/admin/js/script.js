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
    
    WebApi: {
        
        addClient: function(btn) {
            btn.disabled = true;
            var dialog = $('#dialog-clientid');
            var data = dialog.find('form').serialize();
            dialog.find('input').prop('disabled', true);
            $.ajax({
                url: SGA.baseUrl + '/modules/sga.admin/add_oauth_client',
                type: 'post',
                data: data,
                success: function() {
                    SGA.Admin.WebApi.loadClients();
                    dialog.find('input').val('');
                    dialog.modal('hide');
                },
                complete: function() {
                    btn.disabled = false;
                    dialog.find('input').prop('disabled', false);
                }
            });
        },
        
        loadClients: function() {
            $.ajax({
                url: SGA.baseUrl + '/modules/sga.admin/get_all_oauth_client',
                success: function(response) {
                    var table = $('#oauth_clients tbody');
                    table.html('');
                    if (response && response.data) {
                        for (var i = 0; i < response.data.length; i++) {
                            var client = response.data[i];
                            table.append(
                                $('<tr></tr>')
                                    .append('<td>' + client.client_id + '</td>')
                                    .append('<td>' + client.client_secret + '</td>')
                                    .append('<td>' + client.redirect_uri + '</td>')
                                    .append(
                                        $('<td class="buttons"></td>')
                                            .append(
                                                $('<a href="#" data-id="' + client.client_id + '" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span></a>')
                                                .on('click', function() {
                                                    var elem = $(this);
                                                    $.ajax({
                                                        url: SGA.baseUrl + '/modules/sga.admin/get_oauth_client?client_id=' + elem.data('id'),
                                                        success: function(response) {
                                                            var dialog = $('#dialog-clientid');
                                                            if (response && response.data) {
                                                                for (var i in response.data) {
                                                                    dialog.find('#' + i).val(response.data[i]);
                                                                }
                                                            }
                                                            dialog.modal('show');
                                                        }
                                                    });
                                                    return false;
                                                })
                                            )
                                            .append(
                                                $('<a href="#" data-id="' + client.client_id + '" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span></a>')
                                                .on('click', function() {
                                                    if (confirm('Deseja mesmo remover o cliente?')) {
                                                        var elem = $(this);
                                                        $.ajax({
                                                            url: SGA.baseUrl + '/modules/sga.admin/delete_oauth_client',
                                                            data: { client_id: elem.data('id') },
                                                            success: function(response) {
                                                                SGA.Admin.WebApi.loadClients();
                                                            }
                                                        });
                                                    }
                                                    return false;
                                                })
                                            )
                                    )
                            );
                        }
                    }
                }
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
        self.intervalId = self.intervalId || 0;
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