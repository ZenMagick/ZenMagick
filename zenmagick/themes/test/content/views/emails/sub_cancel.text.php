<?php zm_l10n("%s Order Cancel...\n", ZMSettings::get('storeName')) ?>

<?php zm_l10n("Order Details\n") ?>
-----------------------------------------------
<?php zm_l10n("Order Number: #%s\n", $order->getId()) ?>
<?php zm_l10n("Order Date: %s\n", $locale->shortDate($order->getOrderDate())) ?>
