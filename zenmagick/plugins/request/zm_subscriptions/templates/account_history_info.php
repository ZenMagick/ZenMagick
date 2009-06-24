<?php $schedule = $zm_order->getSchedule(); ?>
<?php if (!ZMLangUtils::isEmpty($schedule)) { ?>
    <h3><?php zm_l10n("Subscription") ?></h3>
    <?php $schedules = $zm_subscriptions->getSchedules(); ?>
    <p><?php zm_l10n("Order schedule: '%s.'", $schedules[$schedule]['name']) ?></p>

    <?php if ($zm_order->isSubscription()) { ?>
        <a href="<?php $net->url('cancel_subscription', 'orderId='.$zm_order->getId()) ?>"><?php zm_l10n("Cancel Subscription") ?></a>
    <?php } ?>
<?php } ?>
