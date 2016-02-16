<?php

Weapon::add('shell_after', function() {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'baguetteBox.min.css');
});

Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
    echo Asset::javascript(__DIR__ . DS . 'assets' . DS . 'sword' . DS . 'baguetteBox.min.js');
    echo O_BEGIN . TAB . '<script>baguetteBox.run(\'.image-group\');</script>' . O_END;
});