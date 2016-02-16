(function(w, d, base) {
    if (!base.composer) return;
    var speak = base.languages;
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
        url.onkeydown = function(e) {
            var k = editor.key(e);
            if (!this.value.length && k === 'backspace') return remove(parent), false;
            if (k === 'escape') return base.composer.exit(true), false;
            if (k === 'arrowright' || k === ' ') return this.nextSibling.focus(), false;
            if (e.ctrlKey && k === 'enter' || !this.value.length && k === 'enter') return insert(parent, editor), false;
            if (k === 'enter') return add(parent, editor), false;
        };
        title.onkeydown = function(e) {
            var k = editor.key(e);
            if (k === 'escape') return base.composer.exit(true), false;
            if (k === 'arrowleft' || !this.value.length && k === 'backspace') return this.previousSibling.focus(), false;
            if (e.ctrlKey && k === 'enter' || !this.value.length && k === 'enter') return insert(parent, editor), false;
            if (k === 'enter') return add(parent, editor), false;
        };
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
        var item = parent.children,
            tab = typeof TAB !== "undefined" ? TAB : '  ',
            out = '\n', url, end, child;
        for (var i = 0, len = item.length; i < len; ++i) {
            child = item[i].children;
            if (!child[0].value.length) continue;
            url = ' ' + child[0].value.replace(/\s/g, '+').replace(/\|/g, '%7C');
            end = child[1].value.length ? ' "' + child[1].value.replace(/"/g, '&quot;') + '"' : "";
            out += tab + '[' + (i + 1) + ']:' + url + end + '\n';
        }
        return out === '\n' ? base.composer.exit(true) : editor.tidy('\n\n', function() {
            editor.insert('{{gallery}}' + out + '{{/gallery}}', function() {
                var s = editor.selection();
                if (!s.after.length) editor.area.value += '\n\n';
                editor.select(s.end + 2, true);
            });
        }, '\n\n', true), false;
    }
    // show modal
    function form(e, editor) {
        var btn = speak.MTE.actions,
            s = editor.grip.selection();
        if (s.value.length) {
            return editor.grip.tidy('\n\n', function() {
                editor.grip.wrap('{{gallery}}\n', '\n{{/gallery}}', function() {
                    editor.grip.replace(/^\s*|\s*$/g, "", function() {
                        editor.grip.replace(/\n+/g, '\n');
                    });
                }), false;
            }, '\n\n', true);
        }
        editor.modal('gallery', function(overlay, modal, header, content, footer) {
            header.innerHTML = speak.plugin_gallery.title.modal;
            add(content, editor.grip);
            var o = d.createElement('button'),
                c = d.createElement('button'),
                a = d.createElement('button');
            o.innerHTML = btn.ok;
            c.innerHTML = btn.cancel;
            a.innerHTML = '<i class="fa fa-clone"></i>';
            o.onclick = function() {
                return insert(content, editor.grip), false;
            };
            c.onclick = function() {
                return editor.exit(true), false;
            };
            a.onclick = function() {
                return add(content, editor.grip), false;
            };
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