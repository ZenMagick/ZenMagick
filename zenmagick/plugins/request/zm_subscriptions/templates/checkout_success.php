<?php $schedule = $zm_order->getSchedule(); ?>
<?php if (!ZMTools::isEmpty($schedule)) { ?>
    <h3><?php zm_l10n("Subscription") ?></h3>
    <?php $schedules = $zm_subscriptions->getSchedules(); ?>
    <p><?php zm_l10n("Order schedule: '%s.'", $schedules[$schedule]) ?></p>
<?php } ?>
