
var SGA = SGA || {};

SGA.Atendimento = {
    
    updateGuiche: function(btnLabel) {
        var btns = {};
        btns[btnLabel] = function() {
            var numero = parseInt($('#numero_guiche').val().trim());
            if (!isNaN(numero) && numero > 0) {
                $('#guiche_form').submit();
            } else {
                $('#numero_guiche').val('');
            }
        }
        $('#dialog-guiche').dialog({
            modal: true,
            width: 200,
            buttons: btns
        });
    }
    
}