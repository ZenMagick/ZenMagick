<?php $schedule = $currentOrder->getSchedule(); ?>
<?php if (!ZMLangUtils::isEmpty($schedule)) { ?>
    <h3><?php _vzm("Subscription") ?></h3>
    <?php $schedules = $subscriptions->getSchedules(); ?>
    <p><?php _vzm("Order schedule: '%s.'", $schedules[$schedule]['name']) ?></p>
<?php } ?>
