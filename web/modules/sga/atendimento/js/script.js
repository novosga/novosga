
var SGA = SGA || {};

SGA.Atendimento = {
    
    paused: false,
    ajaxInterval: 3000,
    
    init: function() {
        setInterval(SGA.Atendimento.ajaxUpdate, SGA.Atendimento.ajaxInterval);
        SGA.Atendimento.ajaxUpdate();
    },
    
    ajaxUpdate: function() {
        if (!SGA.Atendimento.paused) {
            $.ajax({
                url: SGA.url('get_fila'),
                success: function(response) {
                    if (response.success) {
                        var list = $("#fila ul");
                        list.text('');
                        for (var i = 0; i < response.atendimentos.length; i++) {
                            var atendimento = response.atendimentos[i];
                            var cssClass = atendimento.prioridade ? 'prioridade' : '';
                            if (i == 0) {
                                cssClass += ' proximo';
                            }
                            var item = '<li class="' + cssClass + '"><abbr title="' + atendimento.servico + '">' + atendimento.numero + '</abbr></li>';
                            list.append(item);
                        }
                    }
                }
            });
        }
    },
    
    updateGuiche: function(btnLabel) {
        var btns = {};
        btns[btnLabel] = function() {
            $('#guiche_form').submit();
        }
        $('#dialog-guiche').dialog({
            modal: true,
            width: 200,
            buttons: btns
        });
    },
    
    chamar: function() {
        
    },
    
    iniciar: function() {
        
    },
    
    encerrar: function() {
        
    }
    
}