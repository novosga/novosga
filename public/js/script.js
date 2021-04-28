/**
 * Novo SGA - Main script
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var App = {
    
    version: 0,
    module: '',
    paused: false,
    updateInterval: 6000,
    dateFormat: '',
    baseUrl: '/',
    
    dialogs: {
        opened: 0
    },
    
    Storage: {
        
        set: function(name, value) {
            if (localStorage) {
                localStorage.setItem(name, value);
            } else {
                // cookie
                var expires = "";
                document.cookie = name + "=" + value + expires + "; path=/";
            }
        },
                
        get: function(name) {
            if (localStorage) {
                return localStorage.getItem(name);
            } else {
                // cookie
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) === ' ') {
                        c = c.substring(1,c.length);
                    }
                    if (c.indexOf(nameEQ) === 0) {
                        return c.substring(nameEQ.length, c.length);
                    }
                }
            }
            return null;
        }
    },
    
    url: function(url) {    
        return App.baseUrl + url;
    },
        
    reload: function() {
        window.location = window.location;
    },
    
    showErrorDialog: function (response) {
        $('#error-modal')
            .data('sessionStatus', response.sessionStatus)
            .modal('show')
            .find('.modal-body>p')
                .text(response.message);
    },
    
    /* jQuery ajax wrapper */
    ajax: function(arg) {
        $('#ajax-loading').show();
        var data = arg.data || {},
            method = arg.type || 'get';
        
        if (method != 'get') {
            data = JSON.stringify(data);
        }
        
        return $.ajax({
            url: arg.url,
            data: data,
            type: method,
            dataType: arg.dataType || 'json',
            contentType: "application/json",
            cache: false,
            success: function(response) {
                if (response && response.success) {
                    var fn = arg.success;
                    if (fn && typeof(fn) === 'function') {
                        fn(response);
                    }
                } else {
                    App.showErrorDialog(response);
                }
                if (response.time) {
                    App.Clock.update(response.time);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var fn = arg.error, json;
                
                try {
                    json = jqXHR.responseJSON || JSON.parse(jqXHR.responseText);
                } catch (e) {
                    json = {
                        message: 'Invalid AJAX response'
                    };
                }
                
                App.showErrorDialog(json);
                
                if (fn && typeof(fn) === 'function') {
                    fn(jqXHR, textStatus, errorThrown);
                }
            },
            complete: function(jqXHR, textStatus) {
                $('#ajax-loading').hide();
                var fn = arg.complete;
                if (fn && typeof(fn) === 'function') {
                    fn(jqXHR, textStatus);
                }
            }
        });
    },
    
    Unidades: {
    
        show: function() {
            $('#dialog-unidade').modal('show');
        },
        
        set: function() {
            var id = $('#unidade').val();
            if (id > 0) {
                App.ajax({
                    url: App.baseUrl + '/set_unidade/' + id,
                    type: 'post',
                    success: function(response) {
                        App.reload();
                    }
                });
            }
        }
        
    },
    
    Clock: {
        
        date: null,
        target: null,
        dateChilds: ['day', 'mon', 'year'],
        timeChilds: ['hours', 'mins', 'secs'],
        
        init: function(targetId, milis) {
            // evitando o parser do jquery para pegar por id
            App.Clock.target = $(document.getElementById(targetId));
            if (App.Clock.target.length > 0) {
                App.Clock.createNodes(App.Clock.target);
                App.Clock.date = new Date(milis);
                App.Clock.update();
                setInterval(App.Clock.update, 1000);
                var separators = App.Clock.target.find('.time .sep');
                setInterval(function() {
                    separators.each(function(i, v) {
                        var node = $(v);
                        var b = node.data('blink') || ' ';
                        node.data('blink', node.text());
                        node.text(b);
                    });
                }, 500);
            }
        },
        
        createNodes: function() {
            var time = $('<div class="time"></div>');
            var date = $('<div class="date"></div>');
            App.Clock._createNodes(time, App.Clock.timeChilds, ':');
            // i18n
            if (App.dateFormat[0] === 'm') {
                // swapping month and day
                var a = App.Clock.dateChilds[0];
                App.Clock.dateChilds[0] = App.Clock.dateChilds[1];
                App.Clock.dateChilds[1] = a;
            }
            App.Clock._createNodes(date, App.Clock.dateChilds, '/');
            App.Clock.target.append(time).append(date);
        },
        
        _createNodes: function(target, childs, sepChar) {
            for (var i = 0; i < childs.length; i++) {
                var c = childs[i];
                App.Clock[c] = $('<span class="dt ' + c + '"></span>');
                target.append(App.Clock[c]);
                if (i < childs.length - 1) {
                    target.append('<span class="sep" data-blink="">' + sepChar + '</span>');
                }
            }
        },
        
        update: function(milis) {
            var c = App.Clock;
            if (c.target) {
                if (milis) {
                    c.date = new Date(milis);
                }
                c.hours.text(App.Clock.zeroFill(c.date.getHours()));
                c.mins.text(App.Clock.zeroFill(c.date.getMinutes()));
                c.secs.text(App.Clock.zeroFill(c.date.getSeconds()));
                c.day.text(App.Clock.zeroFill(c.date.getDate()));
                c.mon.text(App.Clock.zeroFill(c.date.getMonth() + 1));
                c.year.text(c.date.getFullYear());
                // incrementa em 1 segundo
                c.date.setSeconds(c.date.getSeconds() + 1);
            }
        },
        
        zeroFill: function(v) {
            return (v < 10) ? "0" + v : v;
        }
        
    },
            
    Notification: {
        
        request: function(btn) {
            if (Notification) {
                Notification.requestPermission(function(permission) {
                    if (!('permission' in Notification)) {
                        Notification.permission = permission;
                    }
                    $(btn).hide();
                });
            }
        },
        
        allowed: function() {
            if (window.webkitNotifications) {
                return window.webkitNotifications.checkPermission() === 0;
            }
            return Notification && Notification.permission === "granted";
        },

        show: function(title, content) {
            if (this.allowed()) {
                new Notification(title, { body: content, icon: App.baseUrl + '/images/favicon.png' });
            } else {
                this.request();
            }
        }

    },

    SSE: {
        connected: false,
        reconnectAttempts: 0,
        maxReconnectAttempts: 5,
        
        connect (topics) {
            this.url = new URL(document.body.dataset.mercureUrl, window.location.origin);
            for (let topic of topics) {
                this.url.searchParams.append('topic', topic);
            }

            this.eventSource = new EventSource(this.url);

            this.eventSource.onopen = (e) => {
                this.connected = true;
                this.reconnectAttempts = 0;
                this.onopen(e);
            }

            this.eventSource.onerror = (e) => {
                this.connected = false;
                clearTimeout(this.reconnectId);
                this.onerror(e);
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnectAttempts++;
                } else {
                    this.reconnectAttempts = 0;
                    this.ondisconnect();
                }
            }

            this.eventSource.onmessage = (e) => {
                this.connected = true;
                let data;
                try {
                    data = JSON.parse(e.data);
                } catch (ex) {
                    data = null
                }
                this.onmessage(e, data);
            };
        },

        onopen (e) {},
        onerror (e) {},
        onmessage (e, data) {},
        ondisconnect () {}
    }
};

(() => {
    'use strict'
    
    const pingInterval = 10 * 60 * 1000; // 10 minutes

    const doPing = () => {
        if (App.paused) {
            return
        } else {
            App.ajax({
                url: App.url('/ping'),
                error() {
                    window.location.reload()
                }
            })
        }
    }

    setInterval(() => {
        doPing()
    }, pingInterval);
})();

$(function() {
    
    $('div.modal')
    .on('shown.bs.modal', function() {
        App.paused = true;
        App.dialogs.opened++;
    })
    .on('hidden.bs.modal', function() {
        App.dialogs.opened--;
        if (App.dialogs.opened <= 0) {
            App.paused = false;
            App.dialogs.opened = 0;
        }
    })
    .on('hide.bs.modal', function() {
        if ($(this).data('sessionStatus') === 'inactive') {
            window.location.href = App.baseUrl;
        }
    });
    
});