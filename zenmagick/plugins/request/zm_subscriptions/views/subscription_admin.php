<h1>All Subsctiptions</h1>

<?php $zm_resultList->setpagination(4); ?>
<?php if (1 < $zm_resultList->getNumberOfPages()) { ?>
    <div class="rnav">
        <span class="pno"><?php zm_l10n("Page %s/%s", $zm_resultList->getPageNumber(), $zm_resultList->getNumberOfPages()) ?></span>
        <?php if ($zm_resultList->hasPreviousPage()) { ?>
            <a href="<?php $net->resultListBack($zm_resultList) ?>"><?php zm_l10n("Previous") ?></a>&nbsp;
        <?php } else { ?>
            <span class="nin"><?php zm_l10n("Previous") ?></span>&nbsp;
        <?php } ?>
        <?php if ($zm_resultList->hasNextPage()) { ?>
            <a href="<?php $net->resultListNext($zm_resultList) ?>"><?php zm_l10n("Next") ?></a>
        <?php } else { ?>
            <span class="nin"><?php zm_l10n("Next") ?></span>
        <?php } ?>
    </div>
<?php } ?>

<?php $schedules = $plugin->getSchedules(); ?>

<div class="rlist">
    <table cellspacing="0" cellpadding="0" border="1"><thead>
        <tr>
            <th>Order Number</th>
            <th>Frequency</th>
            <th>Next Order</th>
        </tr>
    </thead><tbody>
        <?php $first = true; $odd = true; foreach ($zm_resultList->getResults() as $order) { ?>
            <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
                <td>
                    <a href="<?php $net->url('orders.php', '&action=edit&oID='.$order->getId(), '', true) ?>"><?php zm_l10n("Order #%s", $order->getId()) ?></a>
                </td>
                <td><?php echo $schedules[$order->getSchedule()] ?></td>
                <td><?php $locale->shortDate($order->getNextOrder()) ?></td>
                
            </tr>
        <?php $first = false; $odd = !$odd; } ?>
    </tbody></table>
</div>

