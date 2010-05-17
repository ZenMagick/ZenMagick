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
    <a href="<?php echo $admin2->url('orders', 'orderStatusId='.$status->getOrderStatusId()) ?>"><?php echo $status->getStatusName() ?>: <?php echo $result['count'] ?></a><br>
  <?php } ?>
  </p>
</div>

<br clear="left">
