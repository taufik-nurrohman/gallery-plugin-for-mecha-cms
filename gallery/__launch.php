<?php

Config::merge('DASHBOARD.languages.plugin_gallery', $speak->plugin_gallery);

$f = 'editor.button.' . File::B(__DIR__) . '.min.';

Weapon::add('shell_after', function() use($f) {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . '__gallery.css', "", 'shell/' . $f . 'css');
}, 20);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($f) {
    echo Asset::javascript(__DIR__ . DS . 'assets' . DS . 'sword' . DS . '__gallery.js', "", 'sword/' . $f . 'js');
}, 20);

Route::over($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() {
    File::write(Request::post('css'))->saveTo(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'gallery.css');
    unset($_POST['css']);
});