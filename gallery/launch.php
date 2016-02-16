<?php

$c_gallery = $config->states->{'plugin_' . md5(File::B(__DIR__))};

function do_shortcode_gallery($content) {
    if(strpos($content, '{{/gallery}}') === false) return $content;
    global $config, $c_gallery;
    return preg_replace_callback('#(?<!`)(\{\{gallery(\.([a-zA-Z0-9\-_.]+))?(?:\}\}|\s+.+?\}\}))\s*([\s\S]+?)\s*\{\{\/gallery\}\}(?!`)#', function($matches) use($config, $c_gallery) {
        $e = Converter::attr($matches[1], array('{{', '}}'), array('"', '"', '='), false);
        $w = $c_gallery->thumbnail->w;
        $h = $c_gallery->thumbnail->h;
        $s = $size = "";
        $attr = $e['attributes'];
        $class = ! empty($matches[2]) ? str_replace('.', ' gallery-', $matches[2]) : "";
        if(isset($attr['class'])) {
            $class .= ' ' . $attr['class'];
        }
        if(isset($attr['width'])) {
            $w = (int) $attr['width'];
            $size .= 'width:' . (is_numeric($attr['width']) ? $attr['width'] . 'px' : $attr['width']) . ';';
        }
        if(isset($attr['height'])) {
            $h = (int) $attr['height'];
            $size .= 'height:' . (is_numeric($attr['height']) ? $attr['height'] . 'px' : $attr['height']) . ';';
        }
        unset($attr['class'], $attr['width'], $attr['height']);
        foreach($attr as $k => $v) {
            $s .= ' ' . $k . '="' . $v . '"';
        }
        $t = '/t/' . $w . '/' . $h . '/';
        $size = ! empty($size) ? ' style="' . $size . '"' : "";
        $thumb = Plugin::exist('thumbnail');
        $items = explode("\n", $matches[4]);
        $html = O_BEGIN . '<div class="p gallery' . $class . '"' . $s . '>' . NL;
        $html .= TAB . '<div class="image-group">' . NL;
        foreach($items as $item) {
            if( ! $item = trim($item)) continue;
            if(preg_match('#(.+?)\s+([\'"])(.*?)\2#', $item, $m)) {
                $s = explode(']:', $m[1], 2);
                $src = explode('|', trim($s[1]), 2);
                $src[1] = trim( ! isset($src[1]) && $thumb ? str_replace(File::url(ASSET) . '/', $config->url . $t, $src[0]) : $src[1]);
                $html .= str_repeat(TAB, 2) . '<a href="' . trim($src[0]) . '" title="' . trim($m[3]) . '"' . $size . ' target="_blank">';
                $html .= '<img alt="' . ltrim($s[0], '[') . '" src="' . $src[1] . '" width="' . $w . '" height="' . $h . '"' . ES;
                $html .= '</a>' . NL;
            } else {
                $s = explode(']:', $item, 2);
                $src = explode('|', trim($s[1]), 2);
                $src[1] = trim( ! isset($src[1]) && $thumb ? str_replace(File::url(ASSET) . '/', $config->url . $t, $src[0]) : $src[1]);
                $html .= str_repeat(TAB, 2) . '<a href="' . trim($src[0]) . '"' . $size . ' target="_blank">';
                $html .= '<img alt="' . ltrim($s[0], '[') . '" src="' . $src[1] . '" width="' . $w . '" height="' . $h . '"' . ES;
                $html .= '</a>' . NL;
            }
        }
        return $html . TAB . '</div>' . NL . '</div>' . O_END;
    }, $content);
}

// Apply `do_shortcode_gallery` filter
Filter::add('shortcode', 'do_shortcode_gallery', 20.1);

Weapon::add('shell_after', function() {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'gallery.css', "", 'shell/gallery.min.css');
});

if($c_gallery->lightbox && $path = File::exist(__DIR__ . DS . 'workers' . DS . $c_gallery->lightbox . DS . 'launch.php')) include $path;