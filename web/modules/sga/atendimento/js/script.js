
var SGA = SGA || {};

SGA.Atendimento = {
    
    paused: false,
    ajaxInterval: 3000,
    
    init: function(status) {
        setInterval(SGA.Atendimento.ajaxUpdate, SGA.Atendimento.ajaxInterval);
        SGA.Atendimento.ajaxUpdate();
        SGA.Atendimento.updateControls(status);
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
    
    updateControls: function(status, atendimento) {
        $('#controls button').button();
        $('#controls .control').hide();
        switch (status) {
        case 1: // nenhum atendimento, chamar
            $('#chamar').show();
            break;
        case 2: // senha chamada
            if (atendimento) {
                var info = $('.senha_info');
                info.removeClass('prioridade');
                if (atendimento.prioridade) {
                    info.addClass('prioridade');
                }
                info.find('.numero .value').text(atendimento.numero);
                info.find('.prioridade .value').text(atendimento.nomePrioridade);
                info.find('.servico .value').text(atendimento.servico);
                info.find('.nome .value').text(atendimento.nome);
            }
            $('#iniciar').show();
            break;
        case 3: // atendimento iniciado
            $('#encerrar').show();
            break;
        }
    },
    
    control: function(action, success) {
        $.ajax({
            url: SGA.url(action),
            success: function(response) {
                if (response.success) {
                    success(response);
                } else {
                    alert(response.message);
                }
            }
        });
    },
    
    chamar: function() {
        SGA.Atendimento.control('chamar', function(response) {
            // remove o proximo da lista se for o mesmo do atendimento
            var proximo = $("#fila ul li:first");
            if (response.atendimento.numero == proximo.text()) {
                proximo.remove();
                $("#fila ul li:first").addClass('proximo'); // novo proximo
            }
            SGA.Atendimento.updateControls(2, response.atendimento);
        });
    },
    
    iniciar: function() {
        SGA.Atendimento.control('iniciar', function(response) {
            SGA.Atendimento.updateControls(3, response.atendimento)
        });
    },
    
    encerrar: function() {
        SGA.Atendimento.control('encerrar', function() {
            SGA.Atendimento.updateControls(1)
        });
    }
    
}