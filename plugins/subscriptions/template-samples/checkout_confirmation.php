
<fieldset>
    <legend><?php _vzm("Subscription") ?></legend>
    <div class="btn"><a class="btn" href="<?php echo $net->url('checkout_shipping', '', true) ?>"><?php _vzm("Change") ?></a></div>
    <div>
        <?php $schedule = $subscriptions->getSelectedSchedule(); ?>
        <p><?php _vzm("This order is %s a subscription!", (null != $schedule ? '' : '*NOT*')) ?></p>
        <?php if (null != $schedule) { ?>
            <?php $schedules = $subscriptions->getSchedules(); ?>
            <p><?php _vzm("Order schedule: '%s.'", $schedules[$schedule]['name']) ?></p>
        <?php } ?>
    </div>
</fieldset>

