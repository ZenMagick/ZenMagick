<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
        <h3><a href="#"><?php echo $sub->getName() ?></a></h3>
        <div>
          <ul>
          <?php foreach ($sub->getChildren() as $subItem) { ?>
            <li><a href="<?php echo $admin2->url($subItem->getRequestId(), $subItem->getParams()) ?>"><?php echo $subItem->getName() ?></a></li>
          <?php } ?>
          </ul>
        </div>
      <?php } ?>
    <?php } ?>
  </div>
  <?php if ($root->getId() == 'catalog') {
    echo $this->fetch('catalog-tree.php');
  } ?>
</div>
<script type="text/javascript">
  // hint for navigation matching
  var treatAs = null;
  <?php
    if (null != ($current = $adminMenu->getItemForRequestId($request->getRequestId()))) {
      foreach ($current->getAlias() as $a) {
        if ($request->getRequestId() == $a) {
          echo "treatAs = '".$admin2->url($current->getRequestId(), $current->getParams())."'";
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
        if (treatAs) {
          return -1 < this.href.indexOf(treatAs);
        }
        return this.href == location.href;
      }
		});
	});
</script>
