<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * $Id: zmCacheAdmin.php 2647 2009-11-27 00:30:20Z dermanomann $
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

<h1>Dashboard...</h1>

<!-- TODO: allow to filter status -->
<div class="dbox" style="float:left;border:2px solid #aaa;padding:2px 6px;">
  <h3>Order Stats</h3>
  <p>
  <?php foreach (ZMOrders::instance()->getOrderStatusList($selectedLanguageId) as $status) { ?>
    <?php $result = ZMRuntime::getDatabase()->querySingle("SELECT count(*) AS count FROM " . TABLE_ORDERS . " where orders_status = :orderStatusId", array('orderStatusId' => $status->getOrderStatusId()), TABLE_ORDERS); ?>
    <a href="<?php echo $admin2->url('orders', 'orderStatusId='.$status->getOrderStatusId()) ?>"><?php echo $status->getName() ?>: <?php echo $result['count'] ?></a><br>
  <?php } ?>
  </p>
</div>

<br clear="left">
