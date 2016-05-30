(function(w, d, base) {
    if (!base.composer) return;
    var N = '\n',
        NN = N + N,
        tab = typeof TAB !== "undefined" ? TAB : '  ',
        speak = base.languages;
    // check for image
    function is_image(v) {
        return v.length && (v.indexOf('://') !== -1 || v.match(/\.(bmp|gif|jpe?g|png|webp)$/i));
    }
    // remove field
    function remove(parent) {
        if (parent.children.length > 1) {
            parent.removeChild(parent.lastChild);
            var s = parent.lastChild.lastChild;
            s.focus();
            s.selectionStart = s.selectionEnd = s.value.length; // put cursor at the end of value
        }
    }
    // add field
    function add(parent, editor) {
        var s = speak.MTE.prompts,
            wrap = d.createElement('p'),
            url = d.createElement('input'),
            title = d.createElement('input');
        wrap.className = 'input';
        url.type = 'text';
        if (parent.lastChild && parent.lastChild.firstChild) {
            url.value = base.task.file.D(parent.lastChild.firstChild.value.replace('}}', '}}/')).replace(/[\\\/]+/g, '/').replace(/\/+$/g, "") + '/';
            if (url.value === '/') url.value = "";
        }
        url.placeholder = s.image_url;
        title.placeholder = s.image_title;
        editor.event("keydown", url, function(e) {
            var grip = editor.grip,
                v = this.value,
                k = grip.key(e);
            if (!v.length && k === 'backspace') return remove(parent), false;
            if (k === 'escape') return editor.exit(true), false;
            if (k === 'arrowright' || k === ' ') return this.nextSibling.focus(), false;
            if (k === 'enter' && (e.ctrlKey || !is_image(v))) return insert(parent, editor), false;
            if (k === 'enter') return add(parent, editor), false;
        });
        editor.event("keydown", title, function(e) {
            var v = this.value,
                k = editor.grip.key(e);
            if (k === 'escape') return editor.exit(true), false;
            if (k === 'arrowleft' || !v.length && k === 'backspace') return this.previousSibling.focus(), false;
            if (k === 'enter' && (e.ctrlKey || !v.length)) return insert(parent, editor), false;
            if (k === 'enter') return add(parent, editor), false;
        });
        wrap.appendChild(url);
        wrap.appendChild(title);
        parent.appendChild(wrap);
        w.setTimeout(function() {
            url.focus();
            url.selectionStart = url.selectionEnd = url.value.length; // put cursor at the end of value
        }, .2);
    }
    // insert shortcode
    function insert(parent, editor) {
        var grip = editor.grip,
            item = parent.children,
            out = N, url, end, child;
        if (item.length <= 2) {
            var url = item[0].children[0].value,
                x = item[1] ? item[1].children[0].value : "";
            if (url.length && !is_image(url) && !x) {
                return grip.tidy(NN, function() {
                    grip.insert('{{gallery path="' + url + '"}}', function() {
                        var s = grip.selection();
                        if (!s.after.length) grip.area.value += NN;
                        grip.select(s.end + 2, true);
                    });
                }, NN, true), false;
            }
        }
        var alt = 1;
        for (var i = 0, len = item.length; i < len; ++i) {
            child = item[i].children;
            if (!is_image(child[0].value)) continue;
            url = ' ' + child[0].value.replace(/\s/g, '+').replace(/\|/g, '%7C');
            end = child[1].value.length ? ' "' + child[1].value.replace(/"/g, '&quot;') + '"' : "";
            out += tab + '[' + alt + ']:' + url + end + N;
            alt++;
        }
        return out === N ? editor.exit(true) : grip.tidy(NN, function() {
            grip.insert('{{gallery}}' + out + '{{/gallery}}', function() {
                var s = grip.selection();
                if (!s.after.length) grip.area.value += NN;
                grip.select(s.end + 2, true);
            });
        }, NN, true), false;
    }
    // show modal
    function form(e, editor) {
        var grip = editor.grip,
            btn = speak.MTE.actions,
            s = grip.selection();
        if (s.value.length) {
            return grip.tidy(NN, function() {
                var noop = function() {};
                grip.wrap('{{gallery}}' + N, N + '{{/gallery}}', noop);
                grip.replace(/^\s*|\s*$/g, "", noop);
                grip.replace(/\n+/g, N, noop);
                grip.replace(/^\s*/gm, tab);
            }, NN, true);
        }
        editor.modal('gallery', function(overlay, modal, header, content, footer) {
            header.innerHTML = speak.plugin_gallery.title.modal;
            add(content, editor);
            var o = d.createElement('button'),
                c = d.createElement('button'),
                a = d.createElement('button');
            o.innerHTML = btn.ok;
            c.innerHTML = btn.cancel;
            a.innerHTML = '<i class="fa fa-clone"></i>';
            editor.event("click", o, function() {
                return insert(content, editor), false;
            });
            editor.event("click", c, function() {
                return editor.exit(true), false;
            });
            editor.event("click", a, function() {
                return add(content, editor), false;
            });
            footer.appendChild(o);
            footer.appendChild(c);
            footer.appendChild(a);
        });
    }
    base.composer.button('-gallery plugin-gallery', {
        title: speak.plugin_gallery.title.button,
        click: form
    });
})(window, document, DASHBOARD);