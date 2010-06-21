<?php
/*
 * ZenMagick - Extensions for zen-cart
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
<?php $resources->cssFile('style/dashboard.css'); ?>
<?php $resources->jsFile('js/dashboard.js'); ?>
<h1><?php _vzm('Dashboard') ?></h1>
<?php $adminId = $request->getUser()->getId(); ?>
<?php for ($ii=0; $ii<ZMDashboard::getColumns($adminId); ++$ii) { $widgets = ZMDashboard::getWidgetsForColumn($adminId, $ii); ?>
  <div id="db-column-<?php echo $ii ?>" class="db-column">
    <?php foreach ($widgets as $widgetDef) { ?>
      <?php $widget = ZMBeanUtils::getBean($widgetDef); echo $widget->render($request); ?>
    <?php } ?>
  </div>
<?php } ?>

<p style="clear:left;">Available widgets: <?php echo implode(', ', ZMDashboard::getWidgetList($adminId)) ?></p>
