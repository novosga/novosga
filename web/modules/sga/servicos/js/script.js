/**
 * Novo SGA - Servicos
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Servicos = {
    
    orderTable: function(id) {
        // ordenando a lista via js por limitacoes do dql
        $('#' + id + ' span.sub-servico').each(function(i,e) {
            var item = $(e);
            var parent = $('#' + id + ' span.servico-' + item.data('mestre'));
            if (parent) {
                var next = parent.parent().parent().next();
                while (next.find('span.nome').hasClass('sub-servico')) {
                    next = next.next();
                }
                next.before(item.parent().parent());
            }
        });
        
    }
    
};