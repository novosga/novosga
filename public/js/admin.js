/**
 * Novo SGA - Admin
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var App = App || {};

App.Admin = {
    
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
            var data = App.Admin.Auth.values();
            if (App.Form.checkRequireds('#auth-' + data.type)) {
                App.ajax({
                    url: App.url('auth_save'),
                    type: 'post',
                    data: data,
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
                url: App.baseUrl + '/modules/sga.admin/add_oauth_client',
                type: 'post',
                data: data,
                success: function() {
                    App.Admin.WebApi.loadClients();
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
                url: App.baseUrl + '/modules/sga.admin/get_all_oauth_client',
                success: function(response) {
                    var table = $('#oauth_clients tbody');
                    table.html('');
                    if (response && response.data) {
                        for (var i = 0; i < response.data.length; i++) {
                            var client = response.data[i];
                            var secret = new Array(client.secret.length + 1).join('*');
                            table.append(
                                $('<tr></tr>')
                                    .append('<td>' + client.id + '</td>')
                                    .append('<td><a href="#" data-secret="' + client.secret + '" data-default="' + secret + '">' + secret + '</a></td>')
                                    .append('<td>' + client.redirectUri + '</td>')
                                    .append(
                                        $('<td class="buttons"></td>')
                                            .append(
                                                $('<a href="#" data-id="' + client.id + '" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span></a>')
                                                .on('click', function() {
                                                    var elem = $(this);
                                                    $.ajax({
                                                        url: App.baseUrl + '/modules/sga.admin/get_oauth_client?client_id=' + elem.data('id'),
                                                        success: function(response) {
                                                            var dialog = $('#dialog-clientid');
                                                            if (response && response.data) {
                                                                for (var i in response.data) {
                                                                    dialog.find('#client_' + i).val(response.data[i]);
                                                                }
                                                                dialog.modal('show');
                                                            }
                                                        }
                                                    });
                                                    return false;
                                                })
                                            )
                                            .append(
                                                $('<a href="#" data-id="' + client.id + '" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span></a>')
                                                .on('click', function() {
                                                    if (confirm('Deseja mesmo remover o cliente?')) {
                                                        var elem = $(this);
                                                        $.ajax({
                                                            url: App.baseUrl + '/modules/sga.admin/delete_oauth_client',
                                                            type: 'post',
                                                            data: { client_id: elem.data('id') },
                                                            success: function() {
                                                                App.Admin.WebApi.loadClients();
                                                            }
                                                        });
                                                    }
                                                    return false;
                                                })
                                            )
                                    )
                            );
                        }
                        table.find('[data-secret]').on('click', function() {
                            var elem = $(this);
                            var hide = elem.data('default');
                            if (elem.text() === hide) {
                                elem.text(elem.data('secret'));
                            } else {
                                elem.text(hide);
                            }
                            return false;
                        });
                    }
                }
            });
        }
        
    },
    
    reiniciarSenhas: function(alert) {
        if (confirm(alert)) {
            App.ajax({
                url: App.url('acumular_atendimentos'),
                type: 'post',
                success: function() {
                    $("#dialog-reiniciar").modal('show');
                }
            });
        }
        return false;
    },
    
    limparSenhas: function(alert) {
        if (confirm(alert)) {
            App.ajax({
                url: App.url('limpar_atendimentos'),
                type: 'post',
                success: function() {
                    $("#dialog-limpar").modal('show');
                }
            });
        }
        return false;
    },
    
    changeNumeracao: function() {
        App.ajax({
            url: App.url('change_numeracao'),
            type: 'post',
            data: {
                tipo: $('#numeracao').val()
            },
            success: function() {
            }
        });
    }
};


$(function () {
    $('[type=submit][value=DELETE]').on('click', function (e) {
        return confirm('Você realmente deseja remover?');
    });
});