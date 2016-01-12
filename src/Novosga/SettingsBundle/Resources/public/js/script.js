/**
 * Novo SGA - Unidade
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

App.Unidade = {
    
    Triagem: {
        
        salvar: function() {
            App.ajax({
                url: App.url('update_impressao'),
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
                App.ajax({
                    url: App.url('acumular_atendimentos'),
                    type: 'post',
                    success: function(response) {
                        App.dialogs.modal("#dialog-reiniciar");
                    }
                });
            }
            return false;
        },
    },
        
    Servicos: {
        toggle: function(btn) {
            btn = $(btn);
            App.ajax({
                url: App.url('toggle_servico') + '/' + (btn.prop('checked') ? 1 : 0),
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
            var peso = Math.max(1, $('#peso-' + id).val());
            if (isNaN(peso)) {
                peso = 1;
            }
            $('#peso-' + id).val(peso);
            
            App.ajax({
                url: App.url('update_servico'),
                type: 'post',
                data: {
                    id: id, 
                    sigla: $('#sigla-' + id).val(),
                    local: $('#local-' + id).val(),
                    peso: peso
                }
            });
        }
        
    }
    
};

$(function() {
   $('#servicos input:checkbox').bootstrapSwitch().on('switchChange.bootstrapSwitch', function(event, state) {
       App.Unidade.Servicos.toggle(event.target);
    }); 
});