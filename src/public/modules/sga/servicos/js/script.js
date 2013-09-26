/**
 * Novo SGA - Servicos
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Servicos = {
    
    subservicos: function(id) {
        $('.subservicos').animate({ height: 'hide' });
        var output = $('#servico-' + id);
        output.html('');
        SGA.ajax({
            url: SGA.url('subservicos'),
            data: { id: id },
            success: function(response) {
                var table = $('<table></table>');
                for (var i = 0; i < response.data.length; i++) {
                    var sub = response.data[i];
                    var btnEdit = '<a href="' + SGA.url('edit') + '/' + sub.id + '" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span></a>';
                    table.append('<tr><td>' + sub.nome + '</td><td>' + btnEdit + '</td></tr>');
                }
                output.append(table);
                output.animate({ height: 'show' });
            }
        });
    }
    
};