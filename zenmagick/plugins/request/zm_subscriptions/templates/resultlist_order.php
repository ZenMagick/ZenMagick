        <?php if (!ZMTools::isEmpty($order->getSchedule())) { ?>
            <?php zm_l10n("(S)") ?>
            <?php if ($order->isSubscription()) { ?>
                <?php if ($zm_subscriptions->isCustomerCancel()) { ?>
                    <a href="<?php $net->url(ZM_FILENAME_SUBSCRIPTION_CANCEL, 'orderId='.$order->getId()) ?>"><?php zm_l10n("Cancel Subscription") ?></a>
                <?php } else { ?>
                    <a href="<?php $net->url(ZM_FILENAME_SUBSCRIPTION_REQUEST, 'orderId='.$order->getId().'&type=cancel') ?>"><?php zm_l10n("Cancel Subscription") ?></a>
                <?php } ?>
            <?php } ?>
            <a href="<?php $net->url(ZM_FILENAME_SUBSCRIPTION_REQUEST, 'orderId='.$order->getId().'&type=enquire') ?>"><?php zm_l10n("Ask about Subscriptions") ?></a>
        <?php } ?>

