/**
 * Novo SGA - Main script
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */

const errorModal = new bootstrap.Modal('#error-modal');

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
        set(name, value) {
            if (localStorage) {
                localStorage.setItem(name, value);
            } else {
                // cookie
                var expires = "";
                document.cookie = name + "=" + value + expires + "; path=/";
            }
        },
                
        get(name) {
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
    
    url(url) {    
        return App.baseUrl + url;
    },
        
    reload() {
        window.location = window.location;
    },
    
    showErrorDialog(response) {
        if (response.sessionStatus) {
            errorModal._element.datalist.sessionStatus = response.sessionStatus;
        }
        errorModal._element.querySelector('.modal-body>p').innerText = response.message;
        errorModal.show();
    },

    async ajax(arg) {
        const loading = document.getElementById('ajax-loading');
        loading.style.display = 'inline-block';

        let body = null;
        let headers = {};
        let url = arg.url || '';
        let method = arg.type || arg.method || 'get';
        if (method !== 'get') {
            headers['content-type'] = 'application/json';
            body = JSON.stringify(arg.data);
        } else if (arg.data) {
            const search = new URLSearchParams(arg.data);
            url += '?' + search.toString();
        }

        try {
            const resp = await fetch(url, {
                body,
                method,
                headers,
            });
            const json = await resp.json();
            if (json.success) {
                if (arg.success && typeof(arg.success) === 'function') {
                    arg.success(json, resp);
                }
            } else {
                App.showErrorDialog(json, resp);
            }
            if (json.time) {
                App.Clock.update(json.time);
            }
        } catch (error) {
            App.showErrorDialog(error);
            if (arg.error && typeof(arg.error) === 'function') {
                arg.error(error);
            }
        }

        loading.style.display = 'none';
        if (arg.complete && typeof(arg.complete) === 'function') {
            arg.complete();
        }
    },
    
    Unidades: {
        show() {
            new bootstrap.Modal('#dialog-unidade').show();
        },
        
        set() {
            const id = document.getElementById('unidade').value;
            if (id > 0) {
                App.ajax({
                    url: App.baseUrl + '/set_unidade/' + id,
                    type: 'post',
                    success() {
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
            App.Clock.target = document.getElementById(targetId);
            if (App.Clock.target) {
                App.Clock.createNodes();
                App.Clock.date = new Date(milis);
                App.Clock.update();
                setInterval(App.Clock.update, 1000);
                const separators = [...App.Clock.target.querySelectorAll('.time .sep')];
                setInterval(function() {
                    separators.forEach((elem) => {
                        var b = elem.dataset.blink || ' ';
                        elem.dataset.blink = elem.innerText;
                        elem.innerText = b;
                    });
                }, 500);
            }
        },
        
        createNodes: function() {
            const time = document.createElement('div');
            time.classList.add('time');
            const date = document.createElement('div');
            date.classList.add('date');

            App.Clock._createNodes(time, App.Clock.timeChilds, ':');
            // i18n
            if (App.dateFormat[0] === 'm') {
                // swapping month and day
                var a = App.Clock.dateChilds[0];
                App.Clock.dateChilds[0] = App.Clock.dateChilds[1];
                App.Clock.dateChilds[1] = a;
            }
            App.Clock._createNodes(date, App.Clock.dateChilds, '/');
            App.Clock.target.appendChild(time)
            App.Clock.target.appendChild(date);
        },
        
        _createNodes: function(target, childs, sepChar) {
            for (var i = 0; i < childs.length; i++) {
                var c = childs[i];

                const dt = document.createElement('span');
                dt.classList.add('dt');
                dt.classList.add(c);

                App.Clock[c] = dt;
                target.appendChild(App.Clock[c]);
                if (i < childs.length - 1) {
                    const sep = document.createElement('span');
                    sep.classList.add('sep');
                    sep.innerText = sepChar;
                    target.appendChild(sep);
                }
            }
        },
        
        update: function(milis) {
            var c = App.Clock;
            if (c.target) {
                if (milis) {
                    c.date = new Date(milis);
                }
                c.hours.innerText = App.Clock.zeroFill(c.date.getHours());
                c.mins.innerText = App.Clock.zeroFill(c.date.getMinutes());
                c.secs.innerText = App.Clock.zeroFill(c.date.getSeconds());
                c.day.innerText = App.Clock.zeroFill(c.date.getDate());
                c.mon.innerText = App.Clock.zeroFill(c.date.getMonth() + 1);
                c.year.innerText = c.date.getFullYear();
                // incrementa em 1 segundo
                c.date.setSeconds(c.date.getSeconds() + 1);
            }
        },
        
        zeroFill: function(v) {
            return (v < 10) ? "0" + v : v;
        }
        
    },
            
    Notification: {
        request(btn) {
            if (window.Notification) {
                Notification.requestPermission((permission) => {
                    if (permission === 'granted') {
                        btn.style.display = 'none';
                    } else if (permission === 'denied') {
                        alert('Notificação negada');
                    }
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
    },

    Modal: {
        closeAll() {
            [...document.querySelectorAll('.modal.show')].forEach(elem => {
                const modal = bootstrap.Modal.getInstance(elem);
                modal?.hide();
            });
        }
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
