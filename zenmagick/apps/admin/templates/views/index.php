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
<?php $resources->cssFile('style/dashboard.css'); ?>
<?php $resources->jsFile('js/dashboard.js'); ?>

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
