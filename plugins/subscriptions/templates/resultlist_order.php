<?php use zenmagick\base\Toolbox; ?>

<?php if (!Toolbox::isEmpty($order->getSchedule())) { ?>
    <?php _vzm("(S)") ?>
    <?php if ($order->isSubscription()) { ?>
        <?php if ($subscriptions->isCustomerCancel()) { ?>
            <a href="<?php echo $request->url('cancel_subscription', 'orderId='.$order->getId()) ?>"><?php _vzm("Cancel Subscription") ?></a>
        <?php } else { ?>
            <a href="<?php echo $request->url('subscription_request', 'orderId='.$order->getId().'&type=cancel') ?>"><?php _vzm("Cancel Subscription") ?></a>
        <?php } ?>
    <?php } ?>
    <a href="<?php echo $request->url('subscription_request', 'orderId='.$order->getId().'&type=enquire') ?>"><?php _vzm("Ask about Subscriptions") ?></a>
<?php } ?>

