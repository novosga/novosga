/**
 * Novo SGA - Usuarios
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Usuarios = {

    labelAdd: '',
    labelVisualizarPermissoes: '',
    labelSenhaAlterada: '',
    multiDeleteLabel: '',
    
    multiCheck: function(check, btn) {
        var selClass = (typeof(check) == 'string') ? check : $(check).prop('class');
        btn = (typeof(btn) == 'string') ? $('#' + btn) : $(btn);
        if ($('.' + selClass + ':checked').length > 0) {
            btn.button('enable');
        } else {
            btn.button('disable');
        }
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
    
    delLotacoes: function(btn) {
        return SGA.Usuarios.multiDelete(btn, 'check-lotacao', function(row) {
            var value = row.find('.checkbox .value').val();
            value = value.split(',');
            $('#add-grupo').append('<option value="' + value[0] + '">' + row.find('.grupo').text() + '</option>');
            $('#add-cargo').append('<option value="' + value[1] + '">' + row.find('.cargo').text() + '</option>');
        });
    },
    
    delServicos: function(btn) {
        return SGA.Usuarios.multiDelete(btn, 'check-servico', function() {
            
        });
    },
    
    addDialog: function(id, onclick) {
        var dialog = $("#" + id);
        var props = {buttons: {}};
        props.buttons[SGA.Usuarios.labelAdd] = onclick;
        SGA.dialogs.modal(dialog, props);
        return false;
    },
    
    addLotacao: function() {
        return SGA.Usuarios.addDialog("dialog-add-lotacao", function() {
            var grupo = $('#add-grupo');
            var cargo = $('#add-cargo');
            if (grupo.val() > 0 && cargo.val() > 0) {
                // criando nova linha
                var row = $('<tr><td class="checkbox"></td><td class="grupo"></td><td class="cargo"></td></tr>');
                var value = grupo.val() + "," + cargo.val();
                row.find('.checkbox').append('<input type="checkbox" class="check-lotacao" onchange="SGA.Usuarios.multiCheck(this, \'btn-remove-lotacao\')" />');
                row.find('.checkbox').append('<input type="hidden" class="value" name="lotacoes[]" value="' + value + '" />');
                row.find('.grupo').text(grupo.find('option:selected').text());
                row.find('.cargo').append('<a href="javascript:void(0)" onclick="SGA.Usuarios.permissoes(' + cargo.val() + ')" title="' + SGA.Usuarios.labelVisualizarPermissoes + '">' + cargo.find('option:selected').text() + '</a>');
                $('#lotacoes tbody').append(row);
                cargo.find('option:selected').remove();
                grupo.find('option:selected').remove();
            }
        });
    },
    
    addServico: function() {
        return SGA.Usuarios.addDialog("dialog-add-servico", function() {
            var unidade = $('#add-unidade');
            var servico = $('#add-servico');
            if (unidade.val() > 0 && servico.val() > 0) {
                // criando nova linha
                var row = $('<tr><td class="checkbox"></td><td class="servico"></td><td class="unidade"></td></tr>');
                var value = unidade.val() + "," + servico.val();
                row.find('.checkbox').append('<input type="checkbox" class="check-servico" onchange="SGA.Usuarios.multiCheck(this, \'btn-remove-servico\')" />');
                row.find('.checkbox').append('<input type="hidden" class="value" name="servicos[]" value="' + value + '" />');
                row.find('.servico').text(servico.find('option:selected').text());
                row.find('.unidade').text(unidade.find('option:selected').text());
                $('#servicos tbody').append(row);
                servico.find('option:selected').remove();
            }
        });
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
    
    servicos_unidade: function(unidade) {
        if (unidade > 0) {
            // pegando os ids ja escolhidos para evitar duplicados
            var exceto = [];
            $('#servicos .checkbox .value').each(function(i, e) {
                var value = $(e).val().split(',');
                exceto.push(value[1]);
            });
            SGA.ajax({
                url: SGA.url('servicos_unidade'),
                data: {unidade: unidade, exceto: exceto.join(',')},
                success: function(response) {
                    var unidades = $("#add-servico");
                    var selecione = unidades.find(':first');
                    unidades.html('');
                    unidades.append(selecione);
                    for (var i = 0; i < response.data.length; i++) {
                        unidades.append('<option value="' + response.data[i].id + '">' + response.data[i].nome + '</option>');
                    }
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
            data: {
                id: $('#senha_id').val(),
                senha: $('#senha_senha').val(), 
                confirmacao: $('#senha_confirmacao').val()
            },
            success: function() {
                $('#senha_senha').val('');
                $('#senha_confirmacao').val('');
                alert(SGA.Usuarios.labelSenhaAlterada);
                $('#dialog-senha').dialog('close');
            },
            error: function() {
                $('#senha_senha').val('');
                $('#senha_confirmacao').val('');
            }
        });
        return false;
    }
    
};