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
<div id="sub-menu">
  <div id="sub-common">
    <?php $root = $adminMenu->getRootItemForRequestId($request->getRequestId()); ?>
    <?php if (null != $root) { ?>
      <?php foreach ($root->getChildren() as $sub) { ?>
        <h3><a href="#"><?php echo $sub->getLabel() ?></a></h3>
        <ul>
        <?php foreach ($sub->getChildren() as $subItem) { ?>
          <li><a href="<?php echo $net->url($subItem->getRoute(), $subItem->getRouteParameters()) ?>"><?php echo $subItem->getLabel() ?></a></li>
        <?php } ?>
        </ul>
      <?php } ?>
    <?php } ?>
  </div>
  <?php if (null != $root && $root->getName() == 'catalog') { echo $this->fetch('catalog-tree.html.php'); } ?>
</div>
<script type="text/javascript">
  // hint for navigation matching
  var alias = null;
  <?php
    if (null != ($current = $adminMenu->getItemForRequestId($request->getRequestId()))) {
      foreach ($current->getAlias() as $alias) {
        if ($request->getRequestId() == $alias) {
          echo "alias = '".$net->url($current->getRoute(), true?'':$current->getRouteParameters())."'";
        }
      }
    }
  ?>

  $(function() {
    $("#sub-common").accordion({
      autoHeight: false,
      collapsible: true,
      navigation: true,
      navigationFilter: function() {
        if (alias) {
          return -1 < this.href.indexOf(alias);
        }
        return this.href == location.href;
      }
    });
  });
</script>
