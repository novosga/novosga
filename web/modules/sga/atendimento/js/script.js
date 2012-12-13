/**
 * Novo SGA - Atendimento
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.Atendimento = {
    
    filaVazia: '',
    marcarNaoCompareceu: '',
    marcarErroTriagem: '',
    
    init: function(status) {
        setInterval(SGA.Atendimento.ajaxUpdate, SGA.updateInterval);
        SGA.Atendimento.ajaxUpdate();
        SGA.Atendimento.updateControls(status);
    },
    
    ajaxUpdate: function() {
        if (!SGA.paused) {
            SGA.ajax({
                url: SGA.url('get_fila'),
                success: function(response) {
                    if (response.success) {
                        var list = $("#fila ul");
                        list.text('');
                        if (response.data.length > 0) {
                            for (var i = 0; i < response.data.length; i++) {
                                var atendimento = response.data[i];
                                var cssClass = atendimento.prioridade ? 'prioridade' : '';
                                if (i == 0) {
                                    cssClass += ' proximo';
                                }
                                var item = '<li class="' + cssClass + '"><abbr title="' + atendimento.servico + '">' + atendimento.numero + '</abbr></li>';
                                list.append(item);
                            }
                        } else {
                            list.append('<li class="empty">' + SGA.Atendimento.filaVazia + '</li>')
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
                var info = $('.senha .info');
                info.removeClass('prioridade');
                if (atendimento.prioridade) {
                    info.addClass('prioridade');
                }
                info.find('.numero .value').text(atendimento.numero);
                info.find('.nome-prioridade .value').text(atendimento.nomePrioridade);
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
        SGA.ajax({
            url: SGA.url(action),
            success: function(response) {
                if (response.success) {
                    success(response);
                }
            }
        });
    },
    
    chamar: function() {
        SGA.Atendimento.control('chamar', function(response) {
            // remove o proximo da lista se for o mesmo do atendimento
            var proximo = $("#fila ul li:first");
            if (response.data.numero == proximo.text()) {
                proximo.remove();
                $("#fila ul li:first").addClass('proximo'); // novo proximo
            }
            SGA.Atendimento.updateControls(2, response.data);
        });
    },
    
    iniciar: function() {
        SGA.Atendimento.control('iniciar', function(response) {
            SGA.Atendimento.updateControls(3, response.data)
        });
    },
    
    naocompareceu: function() {
        if (window.confirm(SGA.Atendimento.marcarNaoCompareceu)) {
            SGA.Atendimento.control('naocompareceu', function(response) {
                SGA.Atendimento.updateControls(1, response.data)
            });
        }
    },
    
    encerrar: function() {
        SGA.Atendimento.control('encerrar', function() {
            SGA.Atendimento.updateControls(1)
        });
    },
    
    errotriagem: function() {
        if (window.confirm(SGA.Atendimento.marcarErroTriagem)) {
            SGA.Atendimento.control('errotriagem', function() {
                SGA.Atendimento.updateControls(1)
            });
        }
    }
    
}