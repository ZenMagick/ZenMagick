        <?php if (!ZMLangUtils::isEmpty($order->getSchedule())) { ?>
            <?php zm_l10n("(S)") ?>
            <?php if ($order->isSubscription()) { ?>
                <?php if ($subscriptions->isCustomerCancel()) { ?>
                    <a href="<?php echo $request->url('cancel_subscription', 'orderId='.$order->getId()) ?>"><?php zm_l10n("Cancel Subscription") ?></a>
                <?php } else { ?>
                    <a href="<?php echo $request->url('subscription_request', 'orderId='.$order->getId().'&type=cancel') ?>"><?php zm_l10n("Cancel Subscription") ?></a>
                <?php } ?>
            <?php } ?>
            <a href="<?php echo $request->url('subscription_request', 'orderId='.$order->getId().'&type=enquire') ?>"><?php zm_l10n("Ask about Subscriptions") ?></a>
        <?php } ?>

