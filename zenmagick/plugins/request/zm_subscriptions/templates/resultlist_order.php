        <?php if (!ZMTools::isEmpty($order->getSchedule())) { ?>
            <?php zm_l10n("(S)") ?>
            <?php if ($order->isSubscription()) { ?>
                <a href="<?php $net->url(ZM_FILENAME_SUBSCRIPTION_CANCEL, 'orderId='.$order->getId()) ?>"><?php zm_l10n("Cancel Subscription") ?></a>
            <?php } ?>
        <?php } ?>

