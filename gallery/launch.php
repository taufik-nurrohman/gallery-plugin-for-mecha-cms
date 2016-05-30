<?php

$c_gallery = $config->states->{'plugin_' . md5(File::B(__DIR__))};

function do_shortcode_gallery($content) {
    if( ! Text::check(array('{{/gallery}}', '{{gallery ', '{{gallery.'))->in($content)) return $content;
    global $config, $c_gallery;
    return preg_replace_callback('#(?<!`)(\{\{gallery(\.([-.\w]+))?.*?\}\})(?:(?!`)\s*([^{}]+?)\s*(?<!`)\{\{\/gallery\}\})?(?!`)#', function($matches) use($config, $c_gallery) {
        $e = Converter::attr($matches[1], array('{{', '}}'), array('"', '"', '='), false);
        $w = $c_gallery->thumbnail->w;
        $h = $c_gallery->thumbnail->h;
        $s = $size = "";
        $attr = (array) $e['attributes'];
        // Automatic gallery image(s) from a folder
        if(isset($attr['path']) && $path = File::exist(ASSET . DS . trim($attr['path'], '/'))) {
            if($files = glob($path . DS . '*.{bmp,gif,jpeg,jpg,png,webp}', GLOB_BRACE)) {
                $ss = "";
                foreach($files as $k => $v) {
                    $title = "";
                    $description = $k + 1;
                    $thumbnail = "";
                    if($meta = File::open(preg_replace('#\.(bmp|gif|jpe?g|png|webp)$#', '.txt', $v))->read("")) {
                        $_ = "\n\n" . SEPARATOR . "\n\n";
                        if(strpos($meta, $_) === false) {
                            $meta .= $_;
                        }
                        $meta = Page::text($meta, 'content', 'image:');
                        if(isset($meta['title'])) $title = ' "' . $meta['title'] . '"';
                        if(isset($meta['description'])) $description = $meta['description'];
                        if($t = File::exist($path . DS . 't' . DS . File::B($v))) $thumbnail = ' | ' . File::url($t);
                    }
                    $ss .= '[' . $description . ']: ' . File::url($v) . $thumbnail . $title . "\n";
                }
                // Encode brace(s) in content
                $matches[4] = str_replace(array('{', '}'), array('&#123;', '&#125;'), substr($ss, 0, -1));
            }
        }
        if( ! isset($matches[4])) {
            $matches[4] = "";
        }
        $class = ! empty($matches[2]) ? str_replace('.', ' gallery-', $matches[2]) : "";
        if(isset($attr['class'])) {
            $class .= ' ' . $attr['class'];
        }
        if(isset($attr['width'])) {
            $w = (int) $attr['width'];
            $size .= 'width:' . $attr['width'] . 'px;';
        }
        if(isset($attr['height'])) {
            $h = (int) $attr['height'];
            $size .= 'height:' . $attr['height'] . 'px;';
        }
        unset($attr['path'], $attr['class'], $attr['width'], $attr['height']);
        foreach($attr as $k => $v) {
            $s .= ' ' . $k . '="' . $v . '"';
        }
        $t = '/t/' . $w . '/' . $h . '/';
        $size = ! empty($size) ? ' style="' . $size . '"' : "";
        $thumb = Plugin::exist('thumbnail');
        // Decode brace(s) in content
        $images = str_replace(array('&#123;', '&#125;'), array('{', '}'), $matches[4]);
        $images = explode("\n", $images);
        $html = O_BEGIN . '<div class="p gallery' . $class . '"' . $s . '>' . NL;
        $html .= TAB . '<div class="image-group">' . NL;
        foreach($images as $image) {
            if( ! $image = trim($image)) continue;
            if(strpos($image, ']:') === false) continue;
            if(preg_match('#(.+?)\s+([\'"])(.*?)\2#', $image, $m)) {
                $s = explode(']:', $m[1], 2);
                $s[0] = Text::parse($s[0], '->encoded_html');
                $src = explode('|', trim($s[1]), 2);
                $src[1] = trim( ! isset($src[1]) && $thumb ? str_replace(File::url(ASSET) . '/', $config->url . $t, $src[0]) : (isset($src[1]) ? $src[1] : $src[0]));
                $html .= str_repeat(TAB, 2) . '<a href="' . trim($src[0]) . '" title="' . trim($m[3]) . '"' . $size . ' target="_blank">';
                $html .= '<img alt="' . substr($s[0], 1) . '" src="' . $src[1] . '" width="' . $w . '" height="' . $h . '"' . ES;
                $html .= '</a>' . NL;
            } else {
                $s = explode(']:', $image, 2);
                $s[0] = Text::parse($s[0], '->encoded_html');
                $src = explode('|', trim($s[1]), 2);
                $src[1] = trim( ! isset($src[1]) && $thumb ? str_replace(File::url(ASSET) . '/', $config->url . $t, $src[0]) : (isset($src[1]) ? $src[1] : $src[0]));
                $html .= str_repeat(TAB, 2) . '<a href="' . trim($src[0]) . '"' . $size . ' target="_blank">';
                $html .= '<img alt="' . substr($s[0], 1) . '" src="' . $src[1] . '" width="' . $w . '" height="' . $h . '"' . ES;
                $html .= '</a>' . NL;
            }
        }
        return strpos($html, '<img ') === false ? $matches[0] : $html . TAB . '</div>' . NL . '</div>' . O_END;
    }, $content);
}

// Apply `do_shortcode_gallery` filter
Filter::add('shortcode', 'do_shortcode_gallery', 20.1);

Weapon::add('shell_after', function() {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'gallery.css', "", 'shell/gallery.min.css');
});

if($c_gallery->lightbox && $path = File::exist(__DIR__ . DS . 'workers' . DS . $c_gallery->lightbox . DS . 'launch.php')) include $path;