/**
 * SGA Painel Web
 * @author rogeriolino
 */
var SGA = SGA || {};

SGA.PainelWeb = {
    
    started: false,
    unidade: 0,
    servicos: [],
    senhas: [],
    historico: [],
    ultimoId: 0,
    
    init: function() {
        SGA.PainelWeb.unidade = parseInt(SGA.PainelWeb.Cookie.get('unidade'));
        SGA.PainelWeb.servicos = (SGA.PainelWeb.Cookie.get('servicos') || '').split(',');
        SGA.PainelWeb.started = (SGA.PainelWeb.unidade > 0 && SGA.PainelWeb.servicos.length > 0);
        // exibindo modal de configuracao
        if (!SGA.PainelWeb.started) {
            SGA.PainelWeb.Config.open();
        }
        $(window).on('resize', SGA.PainelWeb.resizer);
        SGA.PainelWeb.resizer();
        setInterval(function() {
            if (SGA.PainelWeb.started) {
                SGA.PainelWeb.ajaxUpdate();
            }
        }, 1000);
        this.chamar();
    },
            
    resizer: function() {
        SGA.PainelWeb.Display.update();
        SGA.PainelWeb.Layout.update();
    },
            
    ajaxUpdate: function() {
        $.ajax({
            url: '../?painel&page=painel_web_update',
            data: {
                unidade: SGA.PainelWeb.unidade,
                servicos: SGA.PainelWeb.servicos.join(',')
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    // no primeiro update só pega a ultima senha (para evitar de ficar chamando senhas antigas)
                    if (SGA.PainelWeb.ultimoId == 0) {
                        var senha = response.data[0];
                        SGA.PainelWeb.senhas.push(senha);
                        SGA.PainelWeb.ultimoId = senha.id;
                    } else {
                        // as senhas estao em ordem decrescente
                        for (var i = response.data.length - 1; i >= 0; i--) {
                            var senha = response.data[i];
                            if (senha.id > SGA.PainelWeb.ultimoId) {
                                SGA.PainelWeb.senhas.push(senha);
                                SGA.PainelWeb.ultimoId = senha.id;
                            }
                        }
                    }
                }
            }
        });
    },
    
    chamar: function() {
        var painel = SGA.PainelWeb;
        if (painel.started && painel.senhas.length > 0) {
            var senha = painel.senhas.shift();
            // atualizando a senha atual
            $('#atual-mensagem span').text(senha.mensagem);
            $('#atual-senha span').text(senha.senha);
            $('#atual-guiche span').text(senha.guiche);
            $('#atual-guiche-numero span').text(senha.numeroGuiche);
            // som e animacao
            document.getElementById("audio-new").play();
            $('#atual-senha').effect("highlight", {
                complete: function() {
                    $('#atual-senha').effect("pulsate", { times: 3 }, 1000);
                }
            }, 500);
            // evita adicionar ao historico rechamadas
            if ($('#atual-senha span').text() != senha.senha) {
                // guardando historico das 10 ultimas senhas
                painel.historico.push(senha); 
                painel.historico = painel.historico.slice(Math.max(0, painel.historico.length - 10), painel.historico.length);
                // atualizando ultimas senhas chamadas
                var senhas = $('#historico .senhas');
                senhas.html('');
                // nao exibe a ultima
                for (var i = painel.historico.length - 2; i >= 0; i--) {
                    var senha = painel.historico[i];
                    var guiche = senha.guiche + ': ' + senha.numeroGuiche;
                    senhas.append('<div class="senha-chamada"><div class="senha fittext"><span>' + senha.senha + '</span></div><div class="guiche fittext"><span>' + guiche + '</span></div></div>');
                }
                SGA.PainelWeb.resizer();
            }
            // se chamou, da um intervalo maior
            setTimeout(SGA.PainelWeb.chamar, 3000);
        } else {
            // nenhuma senha chamada, loop mais rapido
            setTimeout(SGA.PainelWeb.chamar, 500);
        }
    },
    
    Config: {
    
        servicosLoaded: false,
    
        open: function() {
            if (!this.modal) {
                var btns = {};
                btns['Salvar'] = SGA.PainelWeb.Config.save;
                this.modal = $('#config');
                this.modal.dialog({
                    title: 'Config',
                    width: 500,
                    height: 500,
                    modal: true,
                    buttons: btns,
                });
                $('#unidades').val(SGA.PainelWeb.unidade);
                if (SGA.PainelWeb.unidade > 0 && !SGA.PainelWeb.Config.servicosLoaded) {
                    SGA.PainelWeb.Config.loadServicos(SGA.PainelWeb.unidade);
                }
            } else {
                this.modal.dialog('open');
            }
        },
                
        close: function() {
            if (this.modal) {
                this.modal.dialog('close');
            }
        },

        changeUnidade: function() {
            SGA.PainelWeb.Config.servicosLoaded = false;
            var unidade = parseInt($('#unidades').val());
            if (unidade > 0) {
                this.loadServicos(unidade);
            }
        },

        loadServicos: function(unidade) {
            $.ajax({
                url: '../?painel&page=painel_web_servicos',
                data: { unidade: unidade },
                success: function(response) {
                    var servicos = $('#servicos');
                    var html = '<ul>';
                    if (response.success) {
                        for (var i = 0; i < response.data.length; i++) {
                            var servico = response.data[i];
                            var checked = '';
                            for (var j = 0; j < SGA.PainelWeb.servicos.length; j++) {
                                if (servico.id == SGA.PainelWeb.servicos[j]) {
                                    checked = 'checked="checked"';
                                    break;
                                }
                            }
                            html += '<li><label><input type="checkbox" class="servico" value="' + servico.id + '" ' + checked + ' />' + servico.nome + '</label></li>';
                        }
                    }
                    servicos.html(html + '</ul>');
                    SGA.PainelWeb.Config.servicosLoaded = true;
                }
            });
        },
                
        save: function() {
            SGA.PainelWeb.unidade = parseInt($('#unidades').val());
            SGA.PainelWeb.servicos = [];
            $('#servicos input.servico:checked').each(function(i,e) {
                SGA.PainelWeb.servicos.push(parseInt($(e).val()));
            });
            SGA.PainelWeb.started = (SGA.PainelWeb.unidade > 0 && SGA.PainelWeb.servicos.length > 0);
            if (SGA.PainelWeb.started) {
                SGA.PainelWeb.Config.close();
                SGA.PainelWeb.Cookie.add('unidade', SGA.PainelWeb.unidade);
                SGA.PainelWeb.Cookie.add('servicos', SGA.PainelWeb.servicos.join(','));
            }
        }
    },
            
    Layout: {

        update: function() {
            var width = SGA.PainelWeb.Display.width();
            var height = SGA.PainelWeb.Display.height();
            $('#atual-mensagem').textfill({ 
                maxHeight: $('#layout .top').height() 
            });
            $('#atual-senha').textfill({ 
                maxHeight: $('#layout .center').height() 
            });
            $('#atual-guiche').textfill({
                maxWidth: $('#layout .center .right').width() * 0.7,
                maxHeight: $('#layout .center').height() * 0.3
            });
            $('#atual-guiche-numero').textfill({
                maxWidth: $('#layout .center .right').width() * 0.7,
                maxHeight: $('#layout .center').height() * 0.6
            });
            $('#historico .fittext').textfill();
        },
                
        fullscreen: function() {
            SGA.FullScreen.change(function() {
                if (SGA.FullScreen.element()) {
                    $('#btn-fullscreen').hide();
                } else {
                    $('#btn-fullscreen').show();
                }
            });
            SGA.FullScreen.request(document.body);
        }

    },
            
    Display: {

        WIDTH: 800,
        HEIGHT_WIDE: 600,
        HEIGHT_SQUARE: 470,
        
        update: function() {
            d = SGA.PainelWeb.Display;
            d.widthRatio = d.width() / d.WIDTH;
            h = (d.isWide()) ? d.HEIGHT_WIDE : d.HEIGHT_SQUARE;
            d.heightRatio = d.height() / h;
        },
    
        width: function() {
            if (arguments.length > 0) {
                return arguments[0] * this.widthRatio;
            }
            return $(window).width();
        },
        
        height: function() {
            if (arguments.length > 0) {
                return arguments[0] * this.heightRatio;
            }
            return $(window).height();
        },
        
        isWide: function() {
            return this.width() / this.height() > 1.5;
        }
        
    },
            
    Cookie: {

        add: function(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toGMTString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        },
                
        get: function(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') {
                    c = c.substring(1,c.length);
                }
                if (c.indexOf(nameEQ) == 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
            return null;
        }

    }
 
};

(function($) {
    $.fn.textfill = function(options) {
        options = options || {};
        $(this).each(function(i, e) {
            var elem = $(e).find('>span');
            var maxHeight = options.maxHeight || $(this).height();
            var maxWidth = options.maxWidth || $(this).width();
            var fontSize = options.maxFontSize || maxHeight;
            var textHeight;
            var textWidth;
            do {
                elem.css('font-size', fontSize);
                textHeight = elem.height();
                textWidth = elem.width();
                fontSize = fontSize - 5;
            } while ((textHeight > maxHeight || textWidth > maxWidth) && fontSize > 3);
            elem.css('font-size', fontSize * (options.ratio || 1));
        });
        return this;
    }
})(jQuery);

$(document).ready(function() {
    SGA.PainelWeb.init();
    setTimeout(function() {
        $('#menu').fadeTo("slow", 0, function() {
            $('#menu').hover(
                function() {
                    $('#menu').fadeTo("fast", 1);
                }, 
                function() {
                    $('#menu').fadeTo("slow", 0);
                }
            );
        });
    }, 3000);
});