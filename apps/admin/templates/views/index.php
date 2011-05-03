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
<?php $resources->cssFile('style/dashboard.css'); ?>
<?php $resources->jsFile('js/dashboard.js'); ?>
<script>
  function _db_open_options() {
      $('#widget-box').dialog({
          width:500,
          position:['center', 20],
          title:'<?php _vzm('Dashboard Options') ?>'
      }).parents('.ui-dialog').css('overflow', 'visible');
      return false;
  }
</script>

<?php $adminId = $request->getUser()->getId(); ?>
<h1><?php _vzm('Dashboard') ?><a href="" title="<?php _vzm('Customize Dashboard') ?>" onclick="return _db_open_options();"><span class="ui-icon ui-corner-all ui-icon-wrench"></span></a></h1>
<div><!--view-container-->
<div id="dashboard" class="<?php echo (ZMDashboard::getLayout($adminId)) ?>">
  <?php for ($ii=0; $ii<ZMDashboard::getColumns($adminId); ++$ii) { $widgets = ZMDashboard::getWidgetsForColumn($adminId, $ii); ?>
    <div id="db-column-<?php echo $ii ?>" class="db-column">
      <?php foreach ($widgets as $widgetDef) { ?>
        <?php $widget = ZMBeanUtils::getBean($widgetDef); echo $widget->render($request, $view); ?>
      <?php } ?>
    </div>
  <?php } ?>
</div>

<div id="widget-box">
  <div id="grid-list" class="ui-corner-all">
    <a href="" class="db-grid-selector" id="col2"><img src="<?php echo $this->asUrl('images/icons/col2.png', ZMView::RESOURCE) ?>" alt="<?php _vzm('two column') ?>" title="<?php _vzm('two column') ?>"></a>
    <a href="" class="db-grid-selector" id="col2l"><img src="<?php echo $this->asUrl('images/icons/col2l.png', ZMView::RESOURCE) ?>" alt="<?php _vzm('two column - large right') ?>" title="<?php _vzm('two column - large right') ?>"></a>
    <a href="" class="db-grid-selector" id="col2r"><img src="<?php echo $this->asUrl('images/icons/col2r.png', ZMView::RESOURCE) ?>" alt="<?php _vzm('two column - large left') ?>" title="<?php _vzm('two column - large left') ?>"></a>
    <a href="" class="db-grid-selector" id="col3"><img src="<?php echo $this->asUrl('images/icons/col3.png', ZMView::RESOURCE) ?>" alt="<?php _vzm('three column') ?>" title="<?php _vzm('three column') ?>"></a>
  </div>

  <div id="widget-list" class="ui-corner-all">
    <div id="widget-box-cols" class="ui-corner-all">
      <?php $widgetList = ZMDashboard::getWidgetList($adminId); ?>
      <div id="widget-box-col-0" class="widget-box-col">
        <?php for ($ii=0; $ii<count($widgetList); $ii+=2) { 
          $widgetDef = $widgetList[$ii];
          $widget = ZMBeanUtils::getBean($widgetDef); 
          $widget->setOpen(false); echo $widget->render($request, $view);
        } ?>
      </div>
      <div id="widget-box-col-1" class="widget-box-col">
        <?php for ($ii=1; $ii<count($widgetList); $ii+=2) { 
          $widgetDef = $widgetList[$ii];
          $widget = ZMBeanUtils::getBean($widgetDef); 
          $widget->setOpen(false); echo $widget->render($request, $view);
        } ?>
      </div>
    </div>
    <br clear="left">
  </div>
</div>
