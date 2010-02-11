
<fieldset>
    <legend><?php zm_l10n("Subscription") ?></legend>
    <div class="btn"><a class="btn" href="<?php echo $net->url(FILENAME_CHECKOUT_SHIPPING, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
    <div>
        <?php $schedule = $subscriptions->getSelectedSchedule(); ?>
        <p><?php zm_l10n("This order is %s a subscription!", (null != $schedule ? '' : '*NOT*')) ?></p>
        <?php if (null != $schedule) { ?>
            <?php $schedules = $subscriptions->getSchedules(); ?>
            <p><?php zm_l10n("Order schedule: '%s.'", $schedules[$schedule]['name']) ?></p>
        <?php } ?>
    </div>
</fieldset>

