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
<h1>Dashboard</h1>

<style type="text/css">
	.db-column { width: 33%; float: left; padding-bottom: 100px; }
	.portlet { margin: 0 1em 1em 0; }
	.portlet .ui-icon-wrench { display:none; }
	.wrench span.ui-icon-wrench { display:inline; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 0.4em; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }
</style>
<script>
	$(function() {
		$(".db-column").sortable({
      connectWith: '.db-column',
      handle: '.portlet-grip',
      cursor: 'move'
		});

		$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
			.find(".portlet-header")
				.addClass("ui-widget-header ui-corner-all")
        .html(function(index, oldhtml) { return '<div class="portlet-grip">'+oldhtml+'</div>'; })
				.prepend('<span class="ui-icon ui-icon-closethick"></span><span class="ui-icon ui-icon-wrench"></span><span class="ui-icon ui-icon-minusthick"></span>')
				.end()
			.find(".portlet-content");

    // open/close
		$(".portlet-header .ui-icon-minusthick, .portlet-header .ui-icon-plusthick").click(function() {
			$(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
			$(this).parents(".portlet:first").find(".portlet-content").toggle();
		});
    // remove
		$(".portlet-header .ui-icon-closethick").click(function() {
			$(this).parents('.portlet').css('display', 'none');
		});

    $(".portlet-grip").hover(
      function() { $(this).css('cursor', 'move'); }, 
      function() { $(this).css('cursor', 'auto'); }
    );
    
	});
</script>

<?php
  /*

translations:
Latest <a href="<?php echo $admin2->url('orders') ?>"><?php _vzm('Orders') ?></a>

printf('Last %hOrders%%', '<a href="/foo">%h%%</a>');
printf('Letzte %hBestellungen%%', '<a href="/foo">%h%%</a>');

[^%]%([0-9]*)%([^%])+%%


$s = 'Letzte %hBestellungen%%';
preg_match_all('|[^%]%([0-9]*)h(.*[^%])%%|', $s, $matches);
//preg_match_all('|[^%]%|', $s, $matches);
var_dump($matches);
   */


// all defaults
$dashboardConfig = array(
    'columns' => 3,
    'widgets' => array(
        array('OrderStatsDashboardWidget#open=false', 'RecentSearchesDashboardWidget#optionsUrl=abc'),
        array('LatestOrdersDashboardWidget'),
        array('LatestAccountsDashboardWidget')
    )
);

// store widget state in js array
/*
id => (def => class, params => open=false&...),
id => (def => class, params => open=false&...),
id => (def => class, params => open=false&...),

update that with UI events, convert into something like dashboardConfig, jsonify and send to backend
*/

?>
<?php for ($ii=0; $ii < $dashboardConfig['columns']; ++$ii) { $widgets = $dashboardConfig['widgets'][$ii]; ?>
  <div id="db-column-<?php echo $ii ?>" class="db-column">
    <?php foreach ($widgets as $widgetDef) { ?>
      <?php $widget = ZMBeanUtils::getBean($widgetDef); echo $widget->render($request); ?>
    <?php } ?>
  </div>
<?php } ?>
