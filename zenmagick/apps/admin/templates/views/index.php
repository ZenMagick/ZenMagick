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
<?php

  //XXX: centralize 
  if (!$session->getValue('languages_id')) {
      $session->setValue('languages_id', 1);
  }
  $currentLanguage = ZMLanguages::instance()->getLanguageForId($session->getValue('languages_id'));
  $selectedLanguageId = $request->getParameter('languageId', $currentLanguage->getId());

?>

<h1>Dashboard</h1>

	<style type="text/css">
	.db-column { width: 33%; float: left; padding-bottom: 100px; }
	.portlet { margin: 0 1em 1em 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 0.4em; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }
	</style>
	<script type="text/javascript">
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

class ZMDashboardWidget extends ZMWidget {
    private $id_;
    private $minimize_; //boolean
    private $options_; // url
    private $maximize_; // boolean; later
    private $title_;
    private $contents_;
}
   */
  /*
ZMPortletWidget...

views/portlets:

order_stats.php
<!-- minimize=true;options=http://;maximize=false;title=; -->
content.....

   */
?>


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

?>

<div id="db-column-1" class="db-column">
  <?php $portlet = ZMBeanUtils::getBean('OrderStatsDashboardWidget'); ?>
  <?php echo $portlet->render($request); ?>
	
	<div class="portlet">
		<div class="portlet-header">Last 5 search terms</div>
    <div class="portlet-content">
    </div>
	</div>
</div>

<div id="db-column-2" class="db-column">
  <?php $portlet = ZMBeanUtils::getBean('LatestOrdersDashboardWidget'); ?>
  <?php echo $portlet->render($request); ?>
</div>

<div id="db-column-3" class="db-column">
	<div class="portlet">
		<div class="portlet-header">Latest <a href="<?php echo $admin2->url('accounts') ?>"><?php _vzm('Accounts') ?></a></div>
		<div class="portlet-content">
      <table>
      <?php foreach (ZMAccounts::instance()->getAllAccounts(null, 5) as $account) { ?>
        <tr>
          <td><?php echo $account->getId() ?></td>
          <td><a href="<?php echo $admin2->url('account', 'accountId='.$account->getId()) ?>"><?php echo $account->getFullName() ?></a></td>
          <td><?php echo $account->getAccountCreateDate() ?></td>
        </tr>
      <?php } ?>
      </table>
    </div>
	</div>
</div>
