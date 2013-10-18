/**
 * Novo SGA - Unidade
 * @author rogeriolino
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
                    btn.prop("disabled", false)
                }
            });
        },
        enable: function(btn) {
            btn = $(btn);
            btn.prop("disabled", true).addClass('hidden');
            SGA.Unidade.Servicos.request('habilita_servico', btn, function() {
                $('.servico-' + btn.data('id')).prop('disabled', false)
                $('#btn-disable-' + btn.data('id')).prop("disabled", false).removeClass('hidden');
                $('#sigla-' + btn.data('id')).focus();
            });
        },
        
        disable: function(btn) {
            btn = $(btn);
            btn.prop("disabled", true).addClass('hidden');
            SGA.Unidade.Servicos.request('desabilita_servico', btn, function() {
                $('.servico-' + btn.data('id')).prop('disabled', true);
                $('#btn-enable-' + btn.data('id')).prop("disabled", false).removeClass('hidden');
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
                    data: {id: id},
                    type: 'post',
                    success: function(response) {
                        $('#nome-' + id).val(response.data.nome);
                    }
                });
            }
        }
    },
    
};