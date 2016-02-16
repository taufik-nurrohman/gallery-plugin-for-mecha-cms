<?php $c_gallery = $config->states->{'plugin_' . md5(File::B(__DIR__))}; ?>
<label class="grid-group">
  <span class="grid span-2 form-label"><?php echo $speak->plugin_gallery->title->thumbnail->w . ' ' . Jot::info($speak->plugin_gallery->description->thumbnail->w); ?></span>
  <span class="grid span-4"><?php echo Form::number('thumbnail[w]', $c_gallery->thumbnail->w, 72); ?></span>
</label>
<label class="grid-group">
  <span class="grid span-2 form-label"><?php echo $speak->plugin_gallery->title->thumbnail->h . ' ' . Jot::info($speak->plugin_gallery->description->thumbnail->h); ?></span>
  <span class="grid span-4"><?php echo Form::number('thumbnail[h]', $c_gallery->thumbnail->h, 72); ?></span>
</label>
<label class="grid-group">
  <span class="grid span-2 form-label"><?php echo $speak->plugin_gallery->title->lightbox; ?></span>
  <span class="grid span-4">
  <?php

  $_ = 'unit:' . time();
  $options = array();
  foreach(glob(__DIR__ . DS . 'workers' . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $option) {
      $option = File::B($option);
      $options[$option] = isset($speak->plugin_gallery->lightboxes->{$option}) ? $speak->plugin_gallery->lightboxes->{$option} : Text::parse($option, '->title');
  }

  echo Form::select('lightbox', array("" => '&mdash; ' . $speak->none . ' &mdash;') + $options, $c_gallery->lightbox, array('class' => 'input-block'));

  ?>
  </span>
</label>
<div class="grid-group">
  <label class="grid span-2 form-label" for="<?php echo $_; ?>"><?php echo $speak->plugin_gallery->title->css; ?></label>
  <span class="grid span-4"><?php echo Form::textarea('css', File::open(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'gallery.css')->read(), null, array('class' => array('textarea-block', 'textarea-expand', 'code'), 'id' => $_)); ?></span>
</div>
<div class="grid-group">
  <span class="grid span-2"></span>
  <span class="grid span-4"><?php echo Jot::button('action', $speak->update); ?></span>
</div>