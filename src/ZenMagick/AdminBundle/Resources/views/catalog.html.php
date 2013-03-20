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
<?php $view->extend('AdminBundle::default_layout.html.php'); ?>
<?php $admin->title() ?>

<div id="catalog-tabs">
  <ul>
    <?php $tabIndex = 0;
    $index = 0;
    foreach ($controllers as $controller) {
      if ($catalogRequestId == $controller->getCatalogRequestId()) {
        $tabIndex = $index;
        $url = '#'.$catalogRequestId;
      } else {
        $url = $admin->catalogTab($controller);
      }
      ++$index; ?>
      <li><a href="<?php echo $url ?>"><?php echo $controller->getName() ?></a></li>
    <?php } ?>
  </ul>
  <div id="<?php echo $catalogRequestId ?>">
    <?php if (null != $catalogViewContent) { echo $catalogViewContent; } ?>
  </div>
</div>
<script type="text/javascript">
$(function() {
    $("#catalog-tabs").tabs({
      cache: true,
      selected: <?php echo $tabIndex ?>
    });//.css("float", "left");//.css('width', '80%');
});
</script>
