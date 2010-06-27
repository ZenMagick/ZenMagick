<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
  <h3><a href="#">Manage Content</a></h3>
  <div>
    <ul>
    <li><a href="<?php echo $admin2->url('static_page_editor') ?>"><?php _vzm('Static Page Editor') ?></a></li>
      <li><a href="<?php echo $admin2->url('ezpages') ?>"><?php _vzm('EZPages Editor') ?></a></li>
    </ul>
  </div>
  <h3><a href="#">Admin</a></h3>
  <div>
    <ul>
      <li><a href="<?php echo $admin2->url('admin_users') ?>"><?php _vzm('Manage Users') ?></a></li>
    </ul>
  </div>
  <h3><a href="#">Development</a></h3>
  <div>
    <ul>
      <li><a href="<?php echo $admin2->url('l10n') ?>"><?php _vzm('Translation Helper') ?></a></li>
      <li><a href="<?php echo $admin2->url('console') ?>"><?php _vzm('Console') ?></a></li>
    </ul>
  </div>
</div>

<script type="text/javascript">
	$(function() {
		$("#sub-menu").accordion({
			autoHeight: false,
      collapsible: true,
			navigation: true
		});
	});
</script>
