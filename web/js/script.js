/**
 * Novo SGA - Main script
 * @author rogeriolino
 */
var SGA = {
    
    K_MODULE: '',
    K_PAGE: '',
    module: '',
    page: '',
    paused: false,
    updateInterval: 5000,
    
    dialogs: {
        
        opened: 0,
        
        modal: function(target, prop) {
            if (typeof(target) == 'string') {
                target = $(target);
            }
            prop = prop || {};
            prop.title = prop.title || target.prop('title');
            target.dialog({
                width: prop.width || 500,
                height: prop.height || 'auto',
                modal: true,
                title: prop.title,
                buttons: prop.buttons || {},
                create: function(event, ui) {
                    if (typeof(prop.create) == 'function') {
                        prop.create(event, ui);
                    }
                },
                open: function(event, ui) {
                    if (typeof(prop.open) == 'function') {
                        prop.open(event, ui);
                    }
                    SGA.paused = true;
                    SGA.dialogs.opened++;
                },
                close: function(event, ui) {
                    if (typeof(prop.close) == 'function') {
                        prop.close(event, ui);
                    }
                    target.prop('title', prop.title);
                    SGA.dialogs.opened--;
                    if (SGA.dialogs.opened <= 0) {
                        SGA.paused = false;
                        SGA.dialogs.opened = 0;
                    }
                }
            });
        },
        
        error: {
            id: 'dialog-error',
            title: '',
            create: function(prop) {
                var node = $('<div id="' + SGA.dialogs.error.id + '" title="' + SGA.dialogs.error.title + '"><p><span class="ui-icon ui-icon-alert"></span>'+ prop.message +'</p></div>');
                $('body').append(node);
                SGA.dialogs.modal(node, {
                    close: function() {
                        $('#' + SGA.dialogs.error.id).remove();
                        if (typeof(prop.close) == 'function') {
                            prop.close();
                        }
                    }
                });
            }
        }
    },
    
    url: function() {
        var arg = arguments.length > 0 ? arguments[0] : {};
        if (typeof(arg) == 'string') {
            var page = SGA.page;
            arg = {page: arg};
        }
        var url = '';
        if (SGA.module || arg.module) {
            var module = SGA.module || arg.module;
            url += SGA.K_MODULE + '=' + module;
            if (arg.page) {
                var page = SGA.page || arg.page;
                url += '&' + SGA.K_PAGE + '=' + page;
            }
        }
        return '?' + url;
    },
        
    reload: function() {
        window.location = window.location;
    },
    
    formatDate: function(sqlDate) {
        if (sqlDate && sqlDate != "") {
            var d = sqlDate.split(' ');
            var time = '';
            var date = d[0].split('-').reverse().join('/'); // pt_br
            if (d.length > 1) {
                time = ' ' + d[1];
            }
            return date + time;
        }
        return "";
    },
    
    /* jQuery ajax wrapper */
    ajax: function(arg) {
        $('#ajax-loading').show();
        $.ajax({
            url: arg.url,
            data: arg.data || {},
            type: arg.type || 'get',
            dataType: arg.dataType || 'json',
            success: function(response) {
                if (response.success) {
                    var fn = arg.success;
                    if (fn && typeof(fn) == 'function') {
                        fn(response);
                    }
                } else {
                    // checking session
                    if (response.sessionInactive) {
                        SGA.paused = true;
                        SGA.dialogs.error.create({message: SGA.invalidSession, close: function() { SGA.reload(); }});
                    } else {
                        SGA.dialogs.error.create({message: response.message});
                    }
                }
            },
            error: function() {
                var fn = arg.error;
                if (fn && typeof(fn) == 'function') {
                    fn();
                }
            },
            complete: function(response) {
                $('#ajax-loading').hide();
                var fn = arg.complete;
                if (fn && typeof(fn) == 'function') {
                    fn(response);
                }
            }
        });
    },
    
    Form: {
        
        searchBox: function(id) {
            var input = $('#' + id);
            input.on('focus', function() {
                if (!input.data('width')) {
                    input.data('width', input.width());
                }
                input.animate({width: 300});
            });
            input.on('blur', function() {
                input.animate({width: input.data('width')});
            });
        },
        
        validate: function(formId) {
            var form = $('#' + formId);
            form.find(":input:visible:enabled:first").focus();
            form.on('submit', function() {
                var success = true;
                form.find('div.required>:input, :input.required').each(function(i, v) {
                    var input = $(v);
                    if ((input.val() + '').trim() == '') {
                        success = false;
                        var error = input.parent().find('span.error');
                        if (error.length == 0) {
                            error = $('<span class="error"></span>');
                            input.parent().append(error);
                            input.on('change', function() { 
                                if ((input.val() + '').trim() != '') {
                                    error.text('');
                                }
                            });
                        }
                        error.text('Campo obrigatório');
                    }
                });
                return success;
            });
        },
        
        confirm: function(message, success, failure) {
            var r = window.confirm(message);
            if (r && typeof(success) == 'function') {
                success();
            }
            else if (!r && typeof(failure) == 'function') {
                failure();
            }
        },
        
        loginValue: function(input) {
            var value = input.value + "";
            value = value.replace(/([^\w\d])+/g, '');
            input.value = value.toLowerCase();
        }
        
    },
    
    Unidades: {
        
        show: function(id, btnLabel, postUrl) {
            var btns = {};
            btns[btnLabel] = function() {
                SGA.Unidades.set(postUrl);
            }
            SGA.dialogs.modal("#" + id, { width: 450, buttons: btns });
        },
        
        set: function(url) {
            SGA.ajax({
                url: url,
                data: { unidade: $('#unidade').val() },
                dataType: 'json',
                type: 'post',
                success: function(response) {
                    if (response.success) {
                        SGA.reload();
                    }
                }
            })
        }
        
    },
    
    Clock: {
        
        date: null,
        target: null,
        dateChilds: ['day', 'mon', 'year'],
        timeChilds: ['hours', 'mins', 'secs'],
        
        init: function(targetId, milis) {
            // evitando o parser do jquery para pegar por id
            SGA.Clock.target = $(document.getElementById(targetId));
            if (SGA.Clock.target.length > 0) {
                SGA.Clock.createNodes(SGA.Clock.target);
                SGA.Clock.date = new Date(milis);
                SGA.Clock.update();
                setInterval(SGA.Clock.update, 1000);
                var separators = SGA.Clock.target.find('.time .sep');
                setInterval(function() {
                    separators.each(function(i, v) {
                        var node = $(v);
                        var b = node.data('blink');
                        node.data('blink', node.text());
                        node.text(b);
                    });
                }, 500);
            }
        },
        
        createNodes: function() {
            var time = $('<div class="time"></div>');
            var date = $('<div class="date"></div>');
            SGA.Clock._createNodes(time, SGA.Clock.timeChilds, ':');
            SGA.Clock._createNodes(date, SGA.Clock.dateChilds, '/');
            SGA.Clock.target.append(time).append(date);
        },
        
        _createNodes: function(target, childs, sepChar) {
            for (var i = 0; i < childs.length; i++) {
                var c = childs[i];
                SGA.Clock[c] = $('<span class="dt ' + c + '"></span>');
                target.append(SGA.Clock[c]);
                if (i < childs.length - 1) {
                    target.append('<span class="sep" data-blink="">' + sepChar + '</span>');
                }
            }
        },
        
        update: function() {
            var c = SGA.Clock;
            c.hours.text(SGA.Clock.zeroFill(c.date.getHours()));
            c.mins.text(SGA.Clock.zeroFill(c.date.getMinutes()));
            c.secs.text(SGA.Clock.zeroFill(c.date.getSeconds()));
            c.day.text(SGA.Clock.zeroFill(c.date.getDate()));
            c.mon.text(SGA.Clock.zeroFill(c.date.getMonth() + 1));
            c.year.text(c.date.getFullYear());
            // incrementa em 1 segundo
            c.date.setSeconds(c.date.getSeconds() + 1);
        },
        
        zeroFill: function(v) {
            return (v < 10) ? "0" + v : v;
        }
        
    },
    
    Menu: {
        
        init: function(selector) {
            $(selector).each(function () {
                var input = $(this);
                input.on('mouseover mouseout', 'li', function (event) {
                    $(this).children().toggleClass("ui-state-hover", event.type == 'mouseover');
                });
                input.find("li").addClass("ui-state-default");
                input.find("li:last-child").addClass("last-child");
            });
        }
        
    },
    
    DataTable: {
        
        init: function(selector) {
            $(selector).each(function () {
                var input = $(this);
                input.on('mouseover mouseout', 'tbody tr', function (event) {
                    $(this).toggleClass("ui-state-hover", event.type == 'mouseover');
                    $(this).children().toggleClass("ui-state-hover", event.type == 'mouseover');
                });
                input.find("th").addClass("ui-state-default");
                input.find("td").addClass("ui-widget-content");
                input.find("tr:last-child").addClass("last-child");
            });
        }
        
    },
    
    TreeView: {
        
        init: function(selector) {
            var trees = $(selector);
            trees.each(function(i, v) {
                var tree = $(v);
                tree.find('.toggler').each(function(i, v) {
                    var toggler = $(v);
                    var parent = toggler.parent();
                    var childs = SGA.TreeView.childs(tree, parent);
                    if (childs.length > 0) {
                        toggler.on('click', function(e) {
                            for (var i = 0; i < childs.length; i++) {
                                var item = childs[i];
                                if (parent.data('open')) {
                                    SGA.TreeView.close(item);
                                    item.hide();
                                } else {
                                    item.show();
                                    SGA.TreeView.open(item);
                                }
                            }
                            if (parent.data('open')) {
                                SGA.TreeView.close(parent);
                            } else {
                                SGA.TreeView.open(parent);
                            }
                        });
                    } else {
                        toggler.find(".ui-icon").hide();
                        toggler.css("cursor", "default");
                    }
                });
            });
        },
        
        childs: function(tree, parent) {
            var left = parseInt(parent.data('left'));
            var right = parseInt(parent.data('right'));
            var childs = [];
            tree.find('.tree-item').each(function(i, v) {
                var item = $(v);
                // is child
                if (item.data('left') > left && item.data('right') < right) {
                    childs.push(item);
                }
            });
            return childs;
        },
        
        open: function(item) {
            item.data('open', true);
            item.find(".ui-icon").removeClass("ui-icon-triangle-1-e");
            item.find(".ui-icon").addClass("ui-icon-triangle-1-s");
        },
        
        close: function(item) {
            item.data('open', false);
            item.find(".ui-icon").removeClass("ui-icon-triangle-1-s");
            item.find(".ui-icon").addClass("ui-icon-triangle-1-e");
        }
        
    }
    
}

/* helpers */

Array.prototype.contains = function(elem) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == elem) {
            return true;
        }
    }
    return false;
}

/* jquery ext */

jQuery.fn.center = function (type) {
    type = type || 'both';
    if (type == 'both' || type == 'vertical') {
        this.css({
            top: '50%',
            marginTop: '-' + (this.height() / 2) + 'px'
        });
    }
    if (type == 'both' || type == 'horizontal') {
        this.css({
            left: '50%',
            marginLeft: '-' + (this.width() / 2) + 'px'
        });
    }
    this.css("position","absolute");
    return this;
}