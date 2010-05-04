<h1>Dashboard...</h1>

<!-- TODO: allow to filter status -->
<?php foreach (ZMOrders::instance()->getOrderStatusList($session->getValue('languages_id')) as $status) { ?>
  <?php $result = ZMRuntime::getDatabase()->querySingle("SELECT count(*) AS count FROM " . TABLE_ORDERS . " where orders_status = :orderStatusId", array('orderStatusId' => $status->getOrderStatusId()), TABLE_ORDERS); ?>
<p><?php echo $status->getStatusName() ?>: <?php echo $result['count'] ?></p>
<?php } ?>
