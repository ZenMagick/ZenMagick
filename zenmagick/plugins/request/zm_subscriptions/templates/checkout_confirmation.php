
<fieldset>
    <legend><?php zm_l10n("Subscription") ?></legend>
    <div class="btn"><a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_SHIPPING, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
    <div>
        <?php $schedule = $zm_subscriptions->getSelectedSchedule(); ?>
        <p><?php zm_l10n("This order is %s a subscription!", (null != $schedule ? '' : '*NOT*')) ?></p>
        <?php if (null != $schedule) { ?>
            <?php $schedules = $zm_subscriptions->getSchedules(); ?>
            <p><?php zm_l10n("Order schedule: '%s.'", $schedules[$schedule]) ?></p>
        <?php } ?>
    </div>
</fieldset>

