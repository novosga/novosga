/**
 * Novo SGA - Unidade
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Unidade = {
        
    Servicos: {
        request: function(method, btn, complete) {
            btn = $(btn);
            $.ajax({
                url: SGA.url(method),
                data: {id: btn.data('id')},
                type: 'post',
                success: function(response) {
                    complete(response.success);
                },
                error: function() {
                    btn.button("enable")
                }
            });
        },
        enable: function(btn) {
            btn = $(btn);
            btn.button("disable");
            SGA.Unidade.Servicos.request('habilita_servico', btn, function() {
                $('#sigla-' + btn.data('id')).prop('disabled', false).focus();
                $('#btn-disable-' + btn.data('id')).button("enable")
            });
        },
        
        disable: function(btn) {
            btn = $(btn);
            btn.button("disable");
            SGA.Unidade.Servicos.request('desabilita_servico', btn, function() {
                $('#sigla-' + btn.data('id')).prop('disabled', true);
                $('#btn-enable-' + btn.data('id')).button("enable")
            });
        },
        
        updateSigla: function(input) {
            input = $(input);
            $.ajax({
                url: SGA.url('update_sigla'),
                data: {id: input.data('id'), sigla: input.val()},
                type: 'post'
            });
        }
    }
}