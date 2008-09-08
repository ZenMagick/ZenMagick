        <?php if ($order->isSubscription()) { ?>
            <?php zm_l10n("(S)") ?>
            <a href="<?php $net->url(ZM_FILENAME_SUBSCRIPTION_CANCEL, 'orderId='.$order->getId()) ?>"><?php zm_l10n("Cancel Subscription") ?></a>
        <?php } ?>

