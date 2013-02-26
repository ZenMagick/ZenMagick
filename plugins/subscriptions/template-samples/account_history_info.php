<?php use ZenMagick\Base\Toolbox; ?>

<?php $schedule = $currentOrder->getSchedule(); ?>
<?php if (!Toolbox::isEmpty($schedule)) { ?>
    <h3><?php _vzm("Subscription") ?></h3>
    <?php $schedules = $subscriptions->getSchedules(); ?>
    <p><?php _vzm("Order schedule: '%s.'", $schedules[$schedule]['name']) ?></p>

    <?php if ($currentOrder->isSubscription()) { ?>
        <a href="<?php echo $net->url('cancel_subscription', array('orderId' => $currentOrder->getId())) ?>"><?php _vzm("Cancel Subscription") ?></a>
    <?php } ?>
<?php } ?>
