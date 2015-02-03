/**
 * Novo SGA - Usuarios
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

SGA.Usuarios = {

    labelVisualizarPermissoes: '',
    labelSenhaAlterada: '',
    multiDeleteLabel: '',
    
    multiCheck: function(check, btn) {
        setTimeout(function() {
            var selClass = (typeof(check) === 'string') ? check : $(check).prop('class');
            btn = (typeof(btn) === 'string') ? $('#' + btn) : $(btn);
            if ($('.' + selClass + ':checked').length > 0) {
                btn.prop('disabled', false);
            } else {
                btn.prop('disabled', true);
            }
        }, 100);
        return true;
    },
    
    multiDelete: function(btn, selClass, ondelete) {
        if (confirm(SGA.Usuarios.multiDeleteLabel)) {
            $('.' + selClass + ':checked').each(function(i, e) {
                var row = $(e).parent().parent();
                row.remove();
                ondelete(row);
            });
            SGA.Usuarios.multiCheck(selClass, btn);
        }
        return false;
    },
            
    tableCheckAll: function(elem) {
        setTimeout(function() {
            var table = $(elem).parent().parent().parent().parent();
            table.find('tbody input[type="checkbox"]').each(function(i,e) {
                var cb = $(e);
                cb.prop('checked', $(elem).prop('checked'));
                cb.trigger('change');
            });
        }, 100);
        return true;
    },
    
    delLotacoes: function(btn) {
        return SGA.Usuarios.multiDelete(btn, 'check-lotacao', function(row) {
        });
    },
    
    delServicos: function(btn) {
        return SGA.Usuarios.multiDelete(btn, 'check-servico', function() {
        });
    },
    
    addLotacao: function() {
        var grupo = $('#add-grupo');
        var cargo = $('#add-cargo');
        if (grupo.val() > 0 && cargo.val() > 0) {
            // criando nova linha
            var row = $('<tr><td class="check"></td><td class="grupo"></td><td class="cargo"></td></tr>');
            var value = grupo.val() + "," + cargo.val();
            row.find('.check').append('<input type="checkbox" class="check-lotacao" onchange="SGA.Usuarios.multiCheck(this, \'btn-remove-lotacao\')" />');
            row.find('.check').append('<input type="hidden" class="value" name="lotacoes[]" value="' + value + '" />');
            row.find('.grupo').text(grupo.find('option:selected').text());
            row.find('.cargo').append('<a href="javascript:void(0)" onclick="SGA.Usuarios.permissoes(' + cargo.val() + ')" title="' + SGA.Usuarios.labelVisualizarPermissoes + '">' + cargo.find('option:selected').text() + '</a>');
            $('#lotacoes tbody').append(row);
            grupo.find('option:selected').remove();
            $('#dialog-add-lotacao').modal('hide');
        }
    },
    
    addServico: function() {
        var unidade = $('#add-unidade');
        var servicos = [];
        $('#add-servicos :checked').each(function(i,e) {
            servicos.push($(e));
        });
        if (unidade.val() > 0 && servicos.length) {
            for (var i = 0; i < servicos.length; i++) {
                var servico = servicos[i];
                // criando nova linha
                var row = $('<tr><td class="check"></td><td class="servico"></td><td class="unidade"></td></tr>');
                var value = unidade.val() + "," + servico.val();
                row.find('.check').append('<input type="checkbox" class="check-servico" onchange="SGA.Usuarios.multiCheck(this, \'btn-remove-servico\')" />');
                row.find('.check').append('<input type="hidden" class="value" name="servicos[]" value="' + value + '" />');
                row.find('.servico').text(servico.parent().text());
                row.find('.unidade').text(unidade.find('option:selected').text());
                $('#servicos tbody').append(row);
                // removendo item da lista
                servico.parent().parent().remove();
            }
        }
        $("#dialog-add-servico").modal('hide');
    },
    
    permissoes: function(cargo) {
        SGA.ajax({
            url: SGA.url('permissoes_cargo'),
            data: {cargo: cargo},
            success: function(response) {
                var dialog = $("#dialog-permissoes");
                var list = dialog.find('ul');
                list.html('');
                for (var i = 0; i < response.data.length; i++) {
                    list.append('<li>' + response.data[i].nome + '</li>');
                }
                SGA.dialogs.modal(dialog, {});
            }
        });
    },
    
    grupos_disponiveis: function() {
        // pegando os ids ja escolhidos para evitar duplicados
        var exceto = [];
        $('#lotacoes input.value').each(function(i, e) {
            var value = $(e).val().split(',');
            exceto.push(value[0]);
        });
        var grupos = $('#add-grupo');
        var selecione = grupos.find('option[value=""]');
        grupos.html(selecione);
        SGA.ajax({
            url: SGA.url('grupos'),
            data: {exceto: exceto.join(',')},
            success: function(response) {
                for (var i = 0; i < response.data.length; i++) {
                    grupos.append('<option value="' + response.data[i].id + '">' + response.data[i].nome + '</option>');
                }
            }
        });
    },
    
    servicos_unidade: function(unidade) {
        if (unidade > 0) {
            // pegando os ids ja escolhidos para evitar duplicados
            var exceto = [];
            $('#servicos .check .value').each(function(i, e) {
                var value = $(e).val().split(',');
                if (value[0] === unidade) {
                    exceto.push(value[1]);
                }
            });
            SGA.ajax({
                url: SGA.url('servicos_unidade'),
                data: {unidade: unidade, exceto: exceto.join(',')},
                success: function(response) {
                    var servicos = $('<ul></ul>');
                    for (var i = 0; i < response.data.length; i++) {
                        var su = response.data[i];
                        servicos.append('<li><label><input type="checkbox" value="' + su.servico.id + '" />' + su.servico.nome + '</label></li>');
                    }
                    $("#add-servicos").html(servicos);
                }
            });
        }
    },
    
    dialogSenha: function(label) {
        var buttons = {};
        buttons[label] = SGA.Usuarios.alterarSenha;
        SGA.dialogs.modal('#dialog-senha', {
            width: 500,
            buttons: buttons
        });
        return false;
    },
    
    alterarSenha: function() {
        SGA.ajax({
            url: SGA.url('alterar_senha'),
            type: 'post',
            data: {
                id: $('#senha_id').val(),
                senha: $('#senha_senha').val(), 
                confirmacao: $('#senha_confirmacao').val()
            },
            success: function() {
                $('#senha_senha').val('');
                $('#senha_confirmacao').val('');
                alert(SGA.Usuarios.labelSenhaAlterada);
                $('#dialog-senha').modal('hide');
            },
            error: function() {
                $('#senha_senha').val('');
                $('#senha_confirmacao').val('');
            }
        });
        return false;
    }
    
};