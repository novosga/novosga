/**
 * Novo SGA - Modulos
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = SGA || {};

SGA.Modulos = {
    
    Resource: {
        
        load: function(type) {
            SGA.ajax({
                url: SGA.url('load'),
                data: {
                    id: $('#modulo_id').val(),
                    type: type
                },
                success: function(response) {
                    $('textarea#' + type).val(response.data);
                }
            });
        },
        
        save: function(type) {
            SGA.ajax({
                url: SGA.url('save'),
                type: 'post',
                data: {
                    id: $('#modulo_id').val(),
                    type: type,
                    data: $('textarea#' + type).val()
                },
                success: function(response) {
                }
            });
        }
        
    }
    
};