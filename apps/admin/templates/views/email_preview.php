<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php $admin2->title() ?>

<script>
  $(document).ready(function() {
    $('.plink').click(function(evt) {
      evt.stopPropagation();
      $('#preview').attr('src', $(this).attr('href'));
      return false;
    });
  });
</script>

<table class="grid">
  <tr>
    <th><?php _vzm('Template') ?></th>
    <th><?php _vzm('Text') ?></th>
    <th><?php _vzm('HTML') ?></th>
  </tr>
  <?php foreach ($templateInfo as $template => $formats) { ?>
    <tr>
      <td><?php echo $template ?></td>
      <?php
        $textLink = null;
        if (array_key_exists('text', $formats)) {
            $textLink = '<a class="plink" target="_blank" href="'.$admin2->url(null, 'template='.$template.'&format=text&type='.$formats['text']).'">'._zm('Text').'</a>';
        }
      ?>
      <td><?php echo (null != $textLink ? $textLink : '') ?></td>
      <?php
        $htmlLink = null;
        if (array_key_exists('html', $formats)) {
            $htmlLink = '<a class="plink" target="_blank" href="'.$admin2->url(null, 'template='.$template.'&format=html&type='.$formats['html']).'">'._zm('HTML').'</a>';
        }
      ?>
      <td><?php echo (null != $htmlLink ? $htmlLink : '') ?></td>
    </tr>
  <?php } ?>
</table>

<h2><?php _vzm('Preview') ?></h2>
<iframe id="preview" name="preview" width="100%" height="400px" scrolling="auto"></iframe>
