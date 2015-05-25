/**
 * Novo SGA - Main script
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var SGA = {
    
    version: 0,
    module: '',
    paused: false,
    updateInterval: 6000,
    dateFormat: '',
    baseUrl: '/',
    
    dialogs: {
        
        opened: 0,
        
        modal: function(target, prop) {
            if (typeof(target) === 'string') {
                target = $(target);
            }
            prop = prop || {};
            prop.title = prop.title || target.prop('title');
            target.modal();
            if (typeof(prop.create) === 'function') {
                prop.create();
            }
            target.on('shown.bs.modal', function() {
                if (typeof(prop.open) === 'function') {
                    prop.open();
                }
                SGA.paused = true;
                SGA.dialogs.opened++;
            });
            target.on('hidden.bs.modal', function() {
                if (typeof(prop.close) === 'function') {
                    prop.close();
                }
                SGA.dialogs.opened--;
                if (SGA.dialogs.opened <= 0) {
                    SGA.paused = false;
                    SGA.dialogs.opened = 0;
                }
            });
            return target;
        },
        
        error: {
            id: 'dialog-error',
            title: '',
            create: function(prop) {
                var node = $('<div id="' + SGA.dialogs.error.id + '" class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">'+SGA.dialogs.error.title+'</h4></div> <div class="modal-body"><p><span class="glyphicon glyphicon-warning-sign"></span> '+ prop.message +'</p></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button></div></div></div></div>');
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
        if (typeof(arg) === 'string') {
            arg = {page: arg};
        }
        var url = '/';
        if (SGA.module || arg.module) {
            var module = SGA.module || arg.module;
            url += 'modules/' + module;
            if (arg.page) {
                url += '/' + arg.page;
            }
        }
        return SGA.baseUrl + url;
    },
        
    reload: function() {
        window.location = window.location;
    },
            
    secToTime: function(seconds) {
        var hours = Math.floor(seconds / 3600);
        var mins = Math.floor((seconds - (hours * 3600)) / 60);
        mins = mins < 10 ? '0' + mins : mins;
        var secs = Math.floor((seconds - (hours * 3600) - (mins * 60)));
        secs = secs < 10 ? '0' + secs : secs;
        return hours + ":" + mins + ":" + secs;
    },
            
    formatTime: function(sqlDate) {
        if (sqlDate && sqlDate != "") {
            var datetime = sqlDate.split(' ');
            if (datetime.length > 1) {
                // excluindo timezone e microtime
                return datetime[1].substring(0, 8);
            }
        }
        return "";
    },
    
    formatDate: function(sqlDate, onEmpty) {
        if (sqlDate && sqlDate != "") {
            var datetime = sqlDate.split(' ');
            var date = datetime[0].split('-');
            // date i18n
            var format = SGA.dateFormat.toLowerCase().split("/");
            var finalDate = [];
            for (var i = 0; i < format.length; i++) {
                switch (format[i]) {
                case 'd':
                    finalDate[i] = date[2];
                    break;
                case 'm':
                    finalDate[i] = date[1];
                    break;
                case 'y':
                    finalDate[i] = date[0];
                    break;
                }
            }
            var time = '';
            if (datetime.length > 1) {
                // excluindo timezone e microtime
                time = ' ' + datetime[1].substring(0, 8);
            }
            return finalDate.join('/') + time;
        }
        return onEmpty || "";
    },
    
    dateToSql: function(localeDate) {
        if (localeDate && localeDate != "") {
            var datetime = localeDate.split(' ');
            var date = datetime[0].split('/');
            var time = '';
            // date i18n
            var format = SGA.dateFormat.toLowerCase().split("/");
            var sqlDate = [];
            for (var i = 0; i < format.length; i++) {
                switch (format[i]) {
                case 'd':
                    sqlDate[2] = date[i];
                    break;
                case 'm':
                    sqlDate[1] = date[i];
                    break;
                case 'y':
                    sqlDate[0] = date[i];
                    break;
                }
            }
            if (datetime.length > 1) {
                time = ' ' + datetime[1];
            }
            return sqlDate.join('-') + time;
        }
        return "";
    },
    
    /* jQuery ajax wrapper */
    ajax: function(arg) {
        $('#ajax-loading').show();
        var data = arg.data || {};
        data.ts = (new Date()).getTime();
        $.ajax({
            url: arg.url,
            data: data,
            type: arg.type || 'get',
            dataType: arg.dataType || 'json',
            cache: false,
            success: function(response) {
                if (response && response.success) {
                    var fn = arg.success;
                    if (fn && typeof(fn) === 'function') {
                        fn(response);
                    }
                } else {
                    // checking session
                    if (response.inactive || response.invalid) {
                        SGA.paused = true;
                        var message; 
                        if (response.inactive) {
                            message = SGA.inactiveSession;
                        } else {
                            message = SGA.invalidSession;
                        }
                        SGA.dialogs.error.create({
                            message: message, 
                            close: SGA.reload
                        });
                    } else {
                        SGA.dialogs.error.create({
                            message: response.message,
                            close: arg.error
                        });
                    }
                }
                if (response.time) {
                    SGA.Clock.update(response.time);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var fn = arg.error;
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
    
    Form: {
        
        labels: {
            required: '_required_',
        },
        
        searchBox: function(id) {
            var input = $('#' + id);
            input.on('focus', function() {
                if (!input.data('width')) {
                    input.data('width', input.width());
                }
                input.animate({width: 300});
            });
            input.on('blur', function() {
                clearTimeout(this.timeoutId);
                this.timeoutId = setTimeout(function() {
                    input.animate({width: input.data('width')});
                }, 1000);
            });
        },
        
        validate: function(formId) {
            var form = $('#' + formId);
            form.find(":input:visible:enabled:first").focus();
            form.on('submit', function() {
                return SGA.Form.checkRequireds(form);
            });
        },
        
        checkRequireds: function(main) {
            if (typeof(main) === 'string') {
                main = $(main);
            }
            var success = true;
            main.find('div.required>:input, :input.required').each(function(i, v) {
                var input = $(v);
                if ($.trim(input.val()) === '') {
                    success = false;
                    var parent = input.parent();
                    var error = parent.find('span.error');
                    if (error.length === 0) {
                        error = $('<span class="error"></span>');
                        parent.append(error);
                        parent.addClass('has-error');
                        input.on('change', function() { 
                            if ($.trim(input.val()) !== '') {
                                error.text('');
                                parent.removeClass('has-error');
                            }
                        });
                    }
                    error.text(SGA.Form.labels.required);
                }
            });
            return success;
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
            value = value.replace(/([^\w\d\.])+/g, '');
            input.value = value.toLowerCase();
        }
        
    },
            
    Home: {
        filter: function() {
            var searchString = $("#search-input").val() || "";
            $("#modules ul li").each(function(i,e) {
                var item = $(e);
                var name = item.find('.name').text();
                if (SGA.String.search(name, searchString) >= 0) {
                    item.show();
                } else {
                    item.hide();
                }
            });
        }
    },
    
    Unidades: {
    
        show: function() {
            $('#dialog-unidade').modal('show');
        },
        
        set: function() {
            SGA.ajax({
                url: SGA.baseUrl + '/home/set_unidade',
                data: { unidade: $('#unidade').val() },
                type: 'post',
                success: function(response) {
                    SGA.reload();
                }
            });
        }
        
    },
    
    String: {
        
        accented: {
            'A': '[Aa\xaa\xc0-\xc5\xe0-\xe5\u0100-\u0105\u01cd\u01ce\u0200-\u0203\u0226\u0227\u1d2c\u1d43\u1e00\u1e01\u1e9a\u1ea0-\u1ea3\u2090\u2100\u2101\u213b\u249c\u24b6\u24d0\u3371-\u3374\u3380-\u3384\u3388\u3389\u33a9-\u33af\u33c2\u33ca\u33df\u33ff\uff21\uff41]',
            'B': '[Bb\u1d2e\u1d47\u1e02-\u1e07\u212c\u249d\u24b7\u24d1\u3374\u3385-\u3387\u33c3\u33c8\u33d4\u33dd\uff22\uff42]',
            'C': '[Cc\xc7\xe7\u0106-\u010d\u1d9c\u2100\u2102\u2103\u2105\u2106\u212d\u216d\u217d\u249e\u24b8\u24d2\u3376\u3388\u3389\u339d\u33a0\u33a4\u33c4-\u33c7\uff23\uff43]',
            'D': '[Dd\u010e\u010f\u01c4-\u01c6\u01f1-\u01f3\u1d30\u1d48\u1e0a-\u1e13\u2145\u2146\u216e\u217e\u249f\u24b9\u24d3\u32cf\u3372\u3377-\u3379\u3397\u33ad-\u33af\u33c5\u33c8\uff24\uff44]',
            'E': '[Ee\xc8-\xcb\xe8-\xeb\u0112-\u011b\u0204-\u0207\u0228\u0229\u1d31\u1d49\u1e18-\u1e1b\u1eb8-\u1ebd\u2091\u2121\u212f\u2130\u2147\u24a0\u24ba\u24d4\u3250\u32cd\u32ce\uff25\uff45]',
            'F': '[Ff\u1da0\u1e1e\u1e1f\u2109\u2131\u213b\u24a1\u24bb\u24d5\u338a-\u338c\u3399\ufb00-\ufb04\uff26\uff46]',
            'G': '[Gg\u011c-\u0123\u01e6\u01e7\u01f4\u01f5\u1d33\u1d4d\u1e20\u1e21\u210a\u24a2\u24bc\u24d6\u32cc\u32cd\u3387\u338d-\u338f\u3393\u33ac\u33c6\u33c9\u33d2\u33ff\uff27\uff47]',
            'H': '[Hh\u0124\u0125\u021e\u021f\u02b0\u1d34\u1e22-\u1e2b\u1e96\u210b-\u210e\u24a3\u24bd\u24d7\u32cc\u3371\u3390-\u3394\u33ca\u33cb\u33d7\uff28\uff48]',
            'I': '[Ii\xcc-\xcf\xec-\xef\u0128-\u0130\u0132\u0133\u01cf\u01d0\u0208-\u020b\u1d35\u1d62\u1e2c\u1e2d\u1ec8-\u1ecb\u2071\u2110\u2111\u2139\u2148\u2160-\u2163\u2165-\u2168\u216a\u216b\u2170-\u2173\u2175-\u2178\u217a\u217b\u24a4\u24be\u24d8\u337a\u33cc\u33d5\ufb01\ufb03\uff29\uff49]',
            'J': '[Jj\u0132-\u0135\u01c7-\u01cc\u01f0\u02b2\u1d36\u2149\u24a5\u24bf\u24d9\u2c7c\uff2a\uff4a]',
            'K': '[Kk\u0136\u0137\u01e8\u01e9\u1d37\u1d4f\u1e30-\u1e35\u212a\u24a6\u24c0\u24da\u3384\u3385\u3389\u338f\u3391\u3398\u339e\u33a2\u33a6\u33aa\u33b8\u33be\u33c0\u33c6\u33cd-\u33cf\uff2b\uff4b]',
            'L': '[Ll\u0139-\u0140\u01c7-\u01c9\u02e1\u1d38\u1e36\u1e37\u1e3a-\u1e3d\u2112\u2113\u2121\u216c\u217c\u24a7\u24c1\u24db\u32cf\u3388\u3389\u33d0-\u33d3\u33d5\u33d6\u33ff\ufb02\ufb04\uff2c\uff4c]',
            'M': '[Mm\u1d39\u1d50\u1e3e-\u1e43\u2120\u2122\u2133\u216f\u217f\u24a8\u24c2\u24dc\u3377-\u3379\u3383\u3386\u338e\u3392\u3396\u3399-\u33a8\u33ab\u33b3\u33b7\u33b9\u33bd\u33bf\u33c1\u33c2\u33ce\u33d0\u33d4-\u33d6\u33d8\u33d9\u33de\u33df\uff2d\uff4d]',
            'N': '[Nn\xd1\xf1\u0143-\u0149\u01ca-\u01cc\u01f8\u01f9\u1d3a\u1e44-\u1e4b\u207f\u2115\u2116\u24a9\u24c3\u24dd\u3381\u338b\u339a\u33b1\u33b5\u33bb\u33cc\u33d1\uff2e\uff4e]',
            'O': '[Oo\xba\xd2-\xd6\xf2-\xf6\u014c-\u0151\u01a0\u01a1\u01d1\u01d2\u01ea\u01eb\u020c-\u020f\u022e\u022f\u1d3c\u1d52\u1ecc-\u1ecf\u2092\u2105\u2116\u2134\u24aa\u24c4\u24de\u3375\u33c7\u33d2\u33d6\uff2f\uff4f]',
            'P': '[Pp\u1d3e\u1d56\u1e54-\u1e57\u2119\u24ab\u24c5\u24df\u3250\u3371\u3376\u3380\u338a\u33a9-\u33ac\u33b0\u33b4\u33ba\u33cb\u33d7-\u33da\uff30\uff50]',
            'Q': '[Qq\u211a\u24ac\u24c6\u24e0\u33c3\uff31\uff51]',
            'R': '[Rr\u0154-\u0159\u0210-\u0213\u02b3\u1d3f\u1d63\u1e58-\u1e5b\u1e5e\u1e5f\u20a8\u211b-\u211d\u24ad\u24c7\u24e1\u32cd\u3374\u33ad-\u33af\u33da\u33db\uff32\uff52]',
            'S': '[Ss\u015a-\u0161\u017f\u0218\u0219\u02e2\u1e60-\u1e63\u20a8\u2101\u2120\u24ae\u24c8\u24e2\u33a7\u33a8\u33ae-\u33b3\u33db\u33dc\ufb06\uff33\uff53]',
            'T': '[Tt\u0162-\u0165\u021a\u021b\u1d40\u1d57\u1e6a-\u1e71\u1e97\u2121\u2122\u24af\u24c9\u24e3\u3250\u32cf\u3394\u33cf\ufb05\ufb06\uff34\uff54]',
            'U': '[Uu\xd9-\xdc\xf9-\xfc\u0168-\u0173\u01af\u01b0\u01d3\u01d4\u0214-\u0217\u1d41\u1d58\u1d64\u1e72-\u1e77\u1ee4-\u1ee7\u2106\u24b0\u24ca\u24e4\u3373\u337a\uff35\uff55]',
            'V': '[Vv\u1d5b\u1d65\u1e7c-\u1e7f\u2163-\u2167\u2173-\u2177\u24b1\u24cb\u24e5\u2c7d\u32ce\u3375\u33b4-\u33b9\u33dc\u33de\uff36\uff56]',
            'W': '[Ww\u0174\u0175\u02b7\u1d42\u1e80-\u1e89\u1e98\u24b2\u24cc\u24e6\u33ba-\u33bf\u33dd\uff37\uff57]',
            'X': '[Xx\u02e3\u1e8a-\u1e8d\u2093\u213b\u2168-\u216b\u2178-\u217b\u24b3\u24cd\u24e7\u33d3\uff38\uff58]',
            'Y': '[Yy\xdd\xfd\xff\u0176-\u0178\u0232\u0233\u02b8\u1e8e\u1e8f\u1e99\u1ef2-\u1ef9\u24b4\u24ce\u24e8\u33c9\uff39\uff59]',
            'Z': '[Zz\u0179-\u017e\u01f1-\u01f3\u1dbb\u1e90-\u1e95\u2124\u2128\u24b5\u24cf\u24e9\u3390-\u3394\uff3a\uff5a]'
        },
        
        search: function(str, searchString) {
            str = str || "";
            return str.search(SGA.String.pattern(searchString));
        },
        
        pattern: function(searchString) {
            // escape meta characters
            searchString = searchString.replace(/([|()[{.+*?^$\\])/g,"\\$1");
            // split into words
            var words = searchString.split(/\s+/);
            // sort by length
            var length_comp = function (a,b) {
                return b.length - a.length;
            };
            words.sort(length_comp);
            // replace characters by their compositors
            var accent_replacer = function(chr) {
                return SGA.String.accented[chr.toUpperCase()] || chr;
            }
            for (var i = 0; i < words.length; i++) {
                words[i] = words[i].replace(/\S/g,accent_replacer);
            }
            // join as alternatives
            var regexp = words.join("|");
            return new RegExp(regexp,'g');
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
            // i18n
            if (SGA.dateFormat[0] === 'm') {
                // swapping month and day
                var a = SGA.Clock.dateChilds[0];
                SGA.Clock.dateChilds[0] = SGA.Clock.dateChilds[1];
                SGA.Clock.dateChilds[1] = a;
            }
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
        
        update: function(milis) {
            var c = SGA.Clock;
            if (c.target) {
                if (milis) {
                    c.date = new Date(milis);
                }
                c.hours.text(SGA.Clock.zeroFill(c.date.getHours()));
                c.mins.text(SGA.Clock.zeroFill(c.date.getMinutes()));
                c.secs.text(SGA.Clock.zeroFill(c.date.getSeconds()));
                c.day.text(SGA.Clock.zeroFill(c.date.getDate()));
                c.mon.text(SGA.Clock.zeroFill(c.date.getMonth() + 1));
                c.year.text(c.date.getFullYear());
                // incrementa em 1 segundo
                c.date.setSeconds(c.date.getSeconds() + 1);
            }
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
        
    },
    
    Perfil: {
        
        labelSenhaAlterada: '',
        
        dialogSenha: function(label) {
            var buttons = {};
            buttons[label] = SGA.Perfil.alterarSenha;
            SGA.dialogs.modal('#dialog-senha', {
                width: 500,
                buttons: buttons
            });
            return false;
        },

        alterarSenha: function() {
            $('#dialog-message').hide();
            SGA.ajax({
                url: SGA.baseUrl + '/profile/password',
                type: 'post',
                data: {
                    atual: $('#senha_atual').val(), 
                    senha: $('#senha_nova').val(), 
                    confirmacao: $('#senha_confirmacao').val()
                },
                success: function(response) {
                    if (response.success) {
                        SGA.Perfil.clear();
                        $('#dialog-message').html(SGA.Perfil.labelSenhaAlterada)
                                .removeClass('alert-error')
                                .addClass('alert-success')
                                .show();
                    } else {
                        $('#dialog-message').html(response.message)
                                .removeClass('alert-success')
                                .addClass('alert-error')
                                .show();
                    }
                },
                error: function() {
                    return false;
                    SGA.Perfil.clear();
                    $('#dialog-message').hide();
                }
            });
            return false;
        },
        
        clear: function() {
            $('#senha_atual').val('');
            $('#senha_nova').val('');
            $('#senha_confirmacao').val('');
        }
        
    },
    
    FullScreen: {

        toggle: function(elem) {
            if (!SGA.FullScreen.element()) {
                SGA.FullScreen.request(elem);
            } else {
                SGA.FullScreen.cancel();
            }
        },
        
        request: function(elem) {
            if (elem.requestFullScreen) {
                elem.requestFullScreen();
            }
            if (elem.webkitRequestFullScreen) {
                elem.webkitRequestFullScreen();
            }
            if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();
            }
            if (elem.msRequestFullScreen) {
                elem.msRequestFullScreen();
            }
        },
                
        cancel: function() {
            if (document.cancelFullScreen) {
                document.cancelFullScreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            }
        },
        
        enabled: function() {
            if (document.fullscreenEnabled) {
                document.fullscreenEnabled();
            }
            if (document.mozFullScreenEnabled) {
                document.mozFullScreenEnabled();
            }
            if (document.webkitFullscreenEnabled) {
                document.webkitFullscreenEnabled();
            }
            if (document.msFullscreenEnabled) {
                document.msFullscreenEnabled();
            }
        },
        
        change: function(evt) {
            document.addEventListener("fullscreenchange", evt, false);
            document.addEventListener("mozfullscreenchange", evt, false);
            document.addEventListener("webkitfullscreenchange", evt, false);
        },
        
        element: function() {
            return document.fullScreenElement || 
                   document.webkitCurrentFullScreenElement || 
                   document.mozFullScreenElement || 
                   document.msFullScreenElement;
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
                new Notification(title, { body: content, icon: SGA.baseUrl + '/images/favicon.png' });
            } else {
                this.request();
            }
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


$(function() {
    
    $('div.modal')
    .on('shown.bs.modal', function() {
        SGA.paused = true;
        SGA.dialogs.opened++;
    })
    .on('hidden.bs.modal', function() {
        SGA.dialogs.opened--;
        if (SGA.dialogs.opened <= 0) {
            SGA.paused = false;
            SGA.dialogs.opened = 0;
        }
    });
    
});