/**
 * Novo SGA - Unidade
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

SGA.Unidade = {
    
    Triagem: {
        
        salvar: function() {
            SGA.ajax({
                url: SGA.url('update_impressao'),
                type: 'post',
                data: {
                    impressao: $('#impressao:checked').val(),
                    mensagem: $('#mensagem').val()
                },
                success: function(response) {
                    
                }
            });
        },
        
        reiniciarSenhas: function(alert) {
            if (confirm(alert)) {
                SGA.ajax({
                    url: SGA.url('acumular_atendimentos'),
                    type: 'post',
                    success: function(response) {
                        SGA.dialogs.modal("#dialog-reiniciar");
                    }
                });
            }
            return false;
        },
    },
        
    Servicos: {
        init: function() {
            $('#servicos td.nome input').each(function(i,v) {
                var input = $(v);
                input.width(input.parent().width() - 60);
            });
        },
        toggle: function(btn) {
            btn = $(btn);
            SGA.ajax({
                url: SGA.url('toggle_servico') + '/' + (btn.prop('checked') ? 1 : 0),
                data: {id: btn.data('id')},
                type: 'post',
                success: function(response) {
                    if (btn.prop('checked')) {
                        $('.servico-' + btn.data('id')).prop('disabled', false)
                        $('#sigla-' + btn.data('id')).focus();
                    } else {
                        $('.servico-' + btn.data('id')).prop('disabled', true);
                    }
                },
                error: function() {
                    btn.prop("disabled", false)
                }
            });
        },
        
        change: function(id) {
            SGA.ajax({
                url: SGA.url('update_servico'),
                type: 'post',
                data: {
                    id: id, 
                    sigla: $('#sigla-' + id).val(),
                    nome: $('#nome-' + id).val(),
                    local: $('#local-' + id).val()
                }
            });
        },
        
        reverteNome: function(id) {
            if (id > 0) {
                SGA.ajax({
                    url: SGA.url('reverte_nome'),
                    type: 'post',
                    data: {
                        id: id
                    },
                    success: function(response) {
                        $('#nome-' + id).val(response.data.nome);
                    }
                });
            }
        }
    },
    
};

$(function() {
   $('input:checkbox').bootstrapSwitch().on('switchChange.bootstrapSwitch', function(event, state) {
       SGA.Unidade.Servicos.toggle(event.target);
    }); 
});