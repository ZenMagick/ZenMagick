<?php _vzm("%s Order Cancel...\n", ZMSettings::get('storeName')) ?>

<?php _vzm("Order Details\n") ?>
-----------------------------------------------
<?php _vzm("Order Number: #%s\n", $order->getId()) ?>
<?php _vzm("Order Date: %s\n", $locale->shortDate($order->getOrderDate())) ?>
