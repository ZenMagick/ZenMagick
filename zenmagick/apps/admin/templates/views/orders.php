<h1>Orders</h1>
<h2><?php if (null != $orderStatus) { echo $orderStatus->getName(); } ?></h2>

<table>
  <tr>
    <th><?php zm_l10n('ID') ?></th>
    <th><?php zm_l10n('Customer') ?></th>
    <th><?php zm_l10n('Order Date') ?></th>
    <th><?php zm_l10n('Payment') ?></th>
    <th><?php zm_l10n('Shipping') ?></th>
    <th><?php zm_l10n('Total') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $order) { ?>
    <tr>
      <td><?php echo $order->getId() ?></td>
      <td><?php echo $order->getAccount()->getFullName() ?></td>
      <td><?php echo $order->getOrderDate() ?></td>
      <td><?php echo $order->get('payment_method') ?></td>
      <td><?php echo $order->get('shipping_method') ?></td>
      <td><?php echo $order->getTotal() ?></td>
    </tr>
  <?php } ?>
</table>
